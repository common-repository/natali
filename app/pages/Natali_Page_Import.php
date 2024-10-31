<?php

class Natali_Page_Import extends Natali_Abstract_Page
{
    public static $categoryList = [];

    /**
     * @param array $params
     * Рендер страницы импорта
     */
    public function render($params = [])
    {
        $this->disable = false;

        $params = [
            'notices' => []
        ];

        $dbSettings = new Natali_Model_Settings();
        $this->settings = $dbSettings->getAll();

        $cats = new Natali_Model_Categories();
        $this->cat_list = $cats->getAllRows();

        $progress = new Natali_Model_ImportTemp();
        $this->progress = [
            'status' => (int)$dbSettings->get('is_importing'),
            'total'  => count($progress->getNotExistProducts(0)),
            'made'   => count($progress->getRowsFilter(['status' => 1])),
        ];

        $url_rest_api_keys = get_site_url() . '/wp-admin/admin.php?page=wc-settings&tab=advanced&section=keys';

        $this->catList = self::getCategoriesTree();

        if ($this->settings['user_key'] && $this->settings['secret_key']) {
            $this->disable = false;
        } else {
            $this->disable = true;
            $params['notices'][] =
                [
                    'content' => sprintf(
                        __(
                            'Получить <a href="%1s" target="_blank">Api ключи</a> для работы плагина',
                            'wp-natali'
                        ),
                        $url_rest_api_keys
                    ),
                    'type'    => 'error',
                ];
        }

        if (!WOOCOMMERCE_STATUS) {
            $this->disable = true;

            $params['notices'][] =
                [
                    'content' => __('Пожалуйста активируйте Woocommerce', 'wp-natali'),
                    'type'    => 'error',
                ];
        }

        $params['page_title'] = __('Импорт товаров', 'wp-natali');
        $params['template'] = 'pages/import';
        $params['page'] = str_replace('/', '', esc_attr($_GET['page']));
        $params['content'] = [
            'categories' => self::$categoryList,
            'settings'   => $this->cat_list,
            'disable'    => $this->disable
        ];

        parent::render($params);
    }

    public static function finderCat($array, $key, $value)
    {
        if ($array[$key] == $value) {
            return $array['wc_id'];
        } else {
            return '';
        }
    }

    /**
     * @param false $parentId
     * @param int $depth
     * @param false $type
     * @param array $sectionSet
     * @return array|mixed
     * Получить дерево всех категорий натали
     * @throws ErrorException
     */
    public static function getCategoriesTree($parentId = false, $depth = 0, $type = false, $sectionSet = [])
    {
        $apiClient = new Natali_Api_DataBase();
        $urlPath = '/category/list';

        if ($parentId) {
            $urlPath .= '?parentId=' . $parentId;
        }

        $categories = $apiClient->get($urlPath)['data']['categories'] ?? null;

        if (!is_null($categories)) {
            foreach ($categories as &$category) {
                if ($category["categoryId"] > 1000) {
                    continue;
                }

                $category['DEPTH'] = $depth;
                $category['set'] = $sectionSet;

                self::$categoryList[] = $category;
                if ($category['hasSubcategories']) {
                    $newSectionSet = $sectionSet;
                    $newSectionSet[] = $category['title'];

                    $newDepth = $depth + 1;
                    $category["subcategories"] = self::getCategoriesTree(
                        $category["categoryId"],
                        $newDepth,
                        false,
                        $newSectionSet
                    );
                }
            }
        }
        if ($type === 'list') {
            return self::$categoryList;
        }

        return $categories;
    }

    public static function sanitize_array($array)
    {
        $buffer = [];
        foreach ($array as $key => $item) {
            $buffer[$key] = sanitize_text_field($item);
        }

        return $buffer;
    }

    /**
     * Вызываеться при нажатии кнопки сохранить
     */
    public static function ajaxSaveImportSettings()
    {
        $settings = new Natali_Model_Settings();

        $settings->set('importRepeat', sanitize_text_field($_POST['isRepeat']));

        set_time_limit(0);
        $data = self::sanitize_array($_POST['SELECTED_SECTIONS']);
        try {
            $categories = new Natali_Model_Categories();
            $categories->save($data); //Сохраняем настройки из формы в базу данных

            $productList = new Natali_Model_ImportTemp();
            $productList->reset();

            self::saveImportProductsForTemp($data); //Получаем товары из категорий и записываем их во временную таблицу базы данных

            echo json_encode(Natali_Handler::success('Сохранение настроек импорта'));
        } catch (Exception $error) {
            echo json_encode(Natali_Handler::error('Сохранение настроек импорта', $error));
        }

        wp_die();
    }


    public static function saveImportProductsForTemp($data)
    {
        set_time_limit(0);
        $apiClient = new Natali_Api_DataBase();
        $importProducts = [];

        //Фильтруем массив для получения всех выбранных категорий
        $filterData = array_filter($data, ['Natali_Page_Import', 'filterValueNotZero']);

        foreach ($filterData as $catNatali => $catWP) {
            $tmp = $apiClient->get("product/list?categoryId=$catNatali&pageLimit=9999")['data']['products'];

            foreach ($tmp as $item) {
                $importProducts[$catWP][] = $item;
            }
        }

        foreach ($importProducts as $wc_cat_id => $products) {
            foreach ($products as $product) {
                self::saveProductImportTempTable($product, $wc_cat_id);
            }
        }
    }

    public static function getStatusImporting()
    {
        try {
            $productList = new Natali_Model_ImportTemp();
            $settings = new Natali_Model_Settings();
            $total = $productList->getRows(0);

            echo json_encode(
                [
                    'haveImport'   => count($productList->getNotExistProducts(0)),
                    'imported'     => count($productList->getRowsFilter(['status' => 1], 0)),
                    'total'        => count($total),
                    'importRepeat' => $settings->get('importRepeat'),
                    'quantity'     => Natali_Sync_Products::get_count()
                ]
            );
        } catch (Exception $error) {
            echo json_encode(Natali_Handler::error('Получение данных импорта', $error));
        }

        wp_die();
    }

    /**
     * @param $product
     * @param $wc_category_id
     * Сохраняет один товар во временную таблицу импорта
     */
    public static function saveProductImportTempTable($product, $wc_category_id)
    {
        $productTempList = new Natali_Model_ImportTemp();
        $is_imported = wc_get_product_id_by_sku($product['productId']);

        if (!$is_imported && isset($product['productId'])) {
            $params = [
                'product_id'         => $product['productId'],
                'natali_category_id' => implode(',', $product['categoriesIds']),
                'wc_category_id'     => $wc_category_id,
                'status'             => 0
            ];

            $productTempList->insert($params);
        }
    }

    /**
     * @return array
     * Возвращает массив всех настроек импорта
     */
    public static function getSettings()
    {
        global $wpdb;
        return $wpdb->get_results("SELECT * FROM `" . $wpdb->prefix . "natali_import_categories`", ARRAY_A);
    }

    public static function filterValueNotZero($value): bool
    {
        return (bool)((int)$value);
    }

    /**
     * @throws Exception
     */
    public static function start()
    {
        set_time_limit(0);
        //Получение таблицы
        $productList = new Natali_Model_ImportTemp();
        $products = $productList->getNotExistProducts(1);

        if ($products) {
            foreach ($products as $product) {
                $newProduct = new Natali_Product_New($product['product_id'], $product['wc_category_id']);
                $newProduct->create();
                return $newProduct;
            }
        }
        return null;
    }
}
