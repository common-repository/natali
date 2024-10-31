<?php

class Natali_Page_Sync extends Natali_Abstract_Page
{
    public $natali_api;
    public $changer;

    public function render($params = [])
    {
        $this->page_title = 'Синхронизация товаров';
        $this->template = 'pages/sync';
        $this->page = str_replace('/', '', esc_attr($_GET['page']));

        parent::render($this->getData());
    }

    public static function get_change_data_natali()
    {
        $natali_api = new Natali_Api_DataBase();
        $changes = $natali_api->get('product/changes');
        $changes['counter']['published'] = count($changes['published']);
        $changes['counter']['unpublished'] = count($changes['unpublished']);
        $changes['counter']['modify'] = count($changes['modify']);

        if (empty($changes)) {
            return null;
        }

        return $changes;
    }

    /**
     * @throws ErrorException
     */
    public static function get_sync_data(WP_REST_Request $request = null)
    {
        set_time_limit(0);
        $data = self::get_change_data_natali();
        $response = [
            'published'   => [],
            'unpublished' => [],
            'modify'      => [],
        ];

        //Проверяем какие товары нужно удалить с сайта, а не берем весь список удаленных товаров у натали
        foreach ($data['unpublished'] as $product) {
            $wc_product_unpublished_id = wc_get_product_id_by_sku($product['product_id']);

            if ($wc_product_unpublished_id) {
                $response['unpublished'][] = $wc_product_unpublished_id;
            }
        }

        //Проверяем какие товары нужно изменить на сайте, а не берем весь список изменены товаров у натали
        foreach ($data['modify'] as $product) {
            $wc_product_modify_id = wc_get_product_id_by_sku($product['product_id']);

            if ($wc_product_modify_id) {
                $product_modify = get_post($wc_product_modify_id);

                $natali_last_modify = get_post_meta($wc_product_modify_id, 'natali_update');
                if ($natali_last_modify) {
                    $is_modify = (strtotime($natali_last_modify) > strtotime($product_modify->post_modified));
                } else {
                    $is_modify = (strtotime($product['last_modify']) > strtotime($product_modify->post_modified));
                }

                if ($is_modify) {
                    $response['modify'][] = [
                        'wc_id'       => $product_modify->ID,
                        'last_modify' => $product['last_modify'],
                        'product_id'  => $product['product_id']
                    ];
                }
            }
        }

        //Проверяем каких товаров нет на сайте
        foreach ($data['published'] as $product) {
            $wc_product_published_id = wc_get_product_id_by_sku($product['product_id']);

            if (!$wc_product_published_id) {
                $newProduct = new Natali_Product_New($product['product_id']);

                if ($newProduct->get_wc_cat_id()) {
                    $response['published'][] = $product;
                }
            }
        }

        if (is_array($response['published'])) {
            $response['counter']['published'] = count($response['published']);
        }

        if (isset($response['unpublished']) && is_array($response['unpublished'])) {
            $response['counter']['unpublished'] = count($response['unpublished']);
        } else {
            $response['counter']['unpublished'] = 0;
        }

        if (is_array($response['modify'])) {
            $response['counter']['modify'] = count($response['modify']);
        }

        return $response;
    }

    public static function start_sync_cron(WP_REST_Request $request = null)
    {
        set_time_limit(0);
        ini_set("memory_limit", "256M");
        $data = self::get_sync_data();
        try {
            //Удаляем товары
            if ($data['unpublished']) {
                $deletedList = self::delete_products($data['unpublished']);
                $response['delete'] = !is_null($deletedList) ? count($deletedList) : 0;
            } else {
                $response['delete'] = 0;
            }

            if ($data['published']) {
                $createList = self::create_products($data['published']);
                $response['create'] = !is_null($createList) ? count($createList) : 0;
            } else {
                $response['create'] = 0;
            }

            if ($data['modify']) {
                $modifyList = self::modify_products($data['modify']);
                $response['modify'] = !is_null($modifyList) ? count($modifyList) : 0;
            }

            $productList = new Natali_Model_ImportTemp();
            $productList->reset();

            return Natali_Handler::success('Синхронизация', 'Успешно выполнена', $response);
        } catch (Exception $error) {
            return Natali_Handler::error('Синхронизация', $error);
        }
    }

    public static function start_sync(WP_REST_Request $request = null)
    {
        set_time_limit(0);
        $data = $request->get_params();

        try {
            //Удаляем товары
            if ($data['unpublished']) {
                $deletedList = self::delete_products($data['unpublished']);
                $response['deleteList'] = !is_null($deletedList) ? count($deletedList) : 0;
            } else {
                $response['deleteList'] = 0;
            }

            if ($data['published']) {
                $createList = self::create_products($data['published']);
                $response['createList'] = !is_null($createList) ? count($createList) : 0;
            } else {
                $response['createList'] = 0;
            }

            if ($data['modify']) {
                $modifyList = self::modify_products($data['modify']);
                $response['modifyList'] = !is_null($modifyList) ? count($modifyList) : 0;
            }

            return $response;
        } catch (Exception $error) {
            return Natali_Handler::error('Синхронизация', $error);
        }
    }

    public static function import_product(WP_REST_Request $request = null)
    {
        set_time_limit(0);
        $params = $request->get_params();
        try {
            if ($params['productId']) {
                $newProduct = new Natali_Product_New($params['productId']);

                if ($newProduct->create()) {
                    return Natali_Handler::success('Синхронизация', 'Импорт успех', [
                        'id'   => $newProduct->get_wc_id(),
                        'sku'  => $newProduct->get_wc_object()->sku,
                        'name' => $newProduct->get_wc_object()->name,
                    ]);
                }

                return Natali_Handler::success('Синхронизация', 'Ошибка', [
                    'id'      => 0,
                    'sku'     => $params['productId'],
                    'message' => 'Ошибка импорта товара'
                ]);

            } else {
                return Natali_Handler::success('Синхронизация', 'Ошибка', [
                    'message' => 'Не был передан productId'
                ]);
            }

        } catch (Exception $error) {
            return Natali_Handler::error('Синхронизация', $error);
        }
    }

    public static function update_product(WP_REST_Request $request = null)
    {
        $params = $request->get_params();
        try {
            if ($params['productId']) {
                $newProduct = new Natali_Product_New($params['productId']);

                if ($newProduct->update()) {
                    return Natali_Handler::success('Синхронизация', 'Обновление успех', [
                        'id'   => $newProduct->get_wc_id(),
                        'sku'  => $newProduct->get_wc_object()->sku,
                        'name' => $newProduct->get_wc_object()->name,
                    ]);
                }

                return Natali_Handler::success('Синхронизация', 'Обновление Ошибка', [
                    'id'      => 0,
                    'sku'     => $params['productId'],
                    'message' => 'Ошибка импорта товара'
                ]);

            } else {
                return Natali_Handler::success('Синхронизация', 'Обновление Ошибка', [
                    'message' => 'Не был передан productId'
                ]);
            }

        } catch (Exception $error) {
            return Natali_Handler::error('Синхронизация', $error);
        }
    }

    public static function delete_product(WP_REST_Request $request = null)
    {
        $params = $request->get_params();
        $wc = new Natali_Api_WooCommerce();
        $images = [];

        try {
            if ($params['productId']) {
                $id = get_post($params['productId']);

                if ($id) {
                    $deleteProduct = $wc->get("products/{$params['productId']}");

                    if (is_array($deleteProduct->images)) {
                        foreach ($deleteProduct->images as $image) {
                            $images[] = $image->id;
                        }
                    }

                    $result = $wc->delete("products/{$params['productId']}", [
                        'force' => true
                    ]);

                    if ($images) {
                        //Удаляем фотографии
                        foreach ($images as $image) {
                            wp_delete_attachment($image, true);
                        }
                    }

                    return Natali_Handler::success('Синхронизация', 'Удаление успех', [
                        'id'   => $deleteProduct->id,
                        'sku'  => $deleteProduct->sku,
                        'name' => $deleteProduct->name,
                    ]);
                }

                return Natali_Handler::success('Синхронизация', 'Удаление несуществующего', [
                    'id'      => 1,
                    'sku'     => $params['productId'],
                    'message' => 'Удаление из списка'
                ]);

            } else {
                return Natali_Handler::success('Синхронизация', 'Обновление Ошибка', [
                    'message' => 'Не был передан productId'
                ]);
            }

        } catch (Exception $error) {
            return Natali_Handler::error('Синхронизация', $error);
        }
    }

    public static function delete_products($list)
    {
        set_time_limit(0);
        $wooApi = new Natali_Api_WooCommerce();
        $nataliImported = new Natali_Model_ImportedProduct();
        $wooProducts = [];

        // Фильтруем массив по существующим товарам
        foreach ($list as $wc_id) {
            $product = $wooApi->get("products/{$wc_id}");
            try {
                //Удаляем фотографии товара
                foreach ($product->images as &$image) {
                    wp_delete_attachment($image->id, true);
                }

                $response = $wooApi->delete("products/{$wc_id}", [
                    'force' => true  // Удаляем полностью без корзины
                ]);

                if ($response) {
                    $nataliImported->delete(['wc_id' => $wc_id]);
                    $wooProducts[] = $response;
                    Natali_Handler::success(
                        'Удаление',
                        'Успешно',
                        [
                            'wc_id'     => $wc_id,
                            'natali_id' => $product->sku
                        ]
                    );
                }

            } catch (Exception $error) {
                Natali_Log::set("Не удалось удалить товар - $wc_id, {$error->getMessage()}", $error->getCode());
            }
        }
        return $wooProducts;
    }

    /**
     * @throws ErrorException
     * @throws Exception
     */
    public static function create_products($list)
    {
        $response = [];

        foreach ($list as $product) {

            $newProduct = new Natali_Product_New($product['product_id']);
            $newProduct->create();
            $response[] = $newProduct->get_wc_id();

            Natali_Handler::success(
                'Импорт товара',
                'Успешно',
                [
                    'wc_id'     => $newProduct->get_wc_id(),
                    'natali_id' => $product['product_id']
                ]
            );
        }

        return $response;
    }

    /**
     * @throws ErrorException
     */
    public static function modify_products($list)
    {
        $response = [];

        foreach ($list as $product) {
            $updateProduct = new Natali_Product_New($product['product_id']);
            $response[] = $updateProduct->update();

            Natali_Handler::success(
                'Обновление товара',
                'Успешно',
                [
                    'wc_id'     => $updateProduct->get_wc_id(),
                    'natali_id' => $product['product_id']
                ]
            );
        }

        return $response;
    }

    /**
     * Восстановление товара
     */
    public static function repair_product(WP_REST_Request $request = null)
    {
        set_time_limit(0);
        try {
            $data = $request->get_params();

            $counter = Natali_Sync_Products::get_count();

            if ($data['params']['page']) {
                update_option('natali_repair_page', $data['params']['page']);
            }

            $posts = Natali_Sync_Products::get_list(
                [
                    'per_page' => $data['params']['per_page'] ?? 1,
                    'page'     => get_option('natali_repair_page') ?? 1
                ]
            );

            if ($posts) {
                foreach ($posts as $post) {
                    $updateProduct = new Natali_Product_New((int)$post->sku);
                    $updateProduct->update();

                    Natali_Handler::success('Восстановление', 'Успешно', [
                        'id'   => $updateProduct->get_wc_id(),
                        'sku'  => $updateProduct->get_natali_id(),
                        'name' => $updateProduct->get_wc_object()->name,
                    ]);
                }

                return Natali_Handler::success('Восстановление', 'Успешно', [
                    'message'     => 'Успешно выполнено',
                    'repair'      => count($posts),
                    'page'        => $data['params']['page'],
                    'stop'        => false,
                    'allProducts' => $counter
                ]);
            } else {
                return Natali_Handler::success('Восстановление', 'Успешно', [
                    'message'     => 'Все товары восстановлены',
                    'repair'      => 0,
                    'stop'        => true,
                    'allProducts' => $counter
                ]);
            }
        } catch (Exception $error) {
            return Natali_Handler::error('Восстановление', $error);
        }
    }

    /**
     * Получаем данные восстановления товаров из бд или сбрасываем если передан параметр
     *
     * @param WP_REST_Request|null $request
     * @return array
     */
    public static function repair_info(WP_REST_Request $request = null)
    {
        $data = $request->get_params();
        $counter = Natali_Sync_Products::get_count();

        if (isset($data['params']['reset']) && $data['params']['reset']) {
            update_option('natali_repair_page', 1);
        }

        return [
            'page'    => get_option('natali_repair_page'),
            'counter' => $counter
        ];
    }
}