<?php

class Natali_Page_Delete extends Natali_Abstract_Page
{
    /**
     * @var Natali_Model_ImportedProduct
     */
    protected static $dbList;

    public function render($params = [])
    {
        self::$dbList = new Natali_Model_ImportedProduct();
        $settings = new Natali_Model_Settings();

        $params = [
            'page_title' => __('Удаление товаров', 'wp-natali'),
            'template'   => 'pages/delete',
            'content'    => [
                'settings' => $settings->getAll(),
                'count'    => Natali_Sync_Products::get_count(),
            ],
        ];

        $params['page'] = str_replace('/', '', esc_attr($_GET['page']));
        $params['notices'] = [
            [
                'content' => __('Внимание! После нажатия "Начать удаление" будут удалены все товары, 
                которые были импортированы данным модулем, 
                а так же удалит их торговые предложения и все фотографии.', 'wp-natali'),
                'type'    => 'warning',
            ],
        ];

        parent::render($params);
    }

    public static function get_data()
    {
        return [
            'total' => Natali_Sync_Products::get_count(),
        ];
    }

    public static function start(WP_REST_Request $request)
    {
        set_time_limit(0);
        $wc = new Natali_Api_WooCommerce();
        $params = $request->get_params();
        $step = (int)$params['step'];
        $images = [];
        $result = null;
        try {
            $products = Natali_Sync_Products::get_list(['per_page' => $step]);

            if ($products) {
                foreach ($products as $product) {

                    foreach ($product->images as $image) {
                        $images[] = $image->id;
                    }

                    $result = $wc->delete("products/{$product->id}", [
                        'force' => true
                    ]);

                    if ($result) {
                        Natali_Handler::success('Удаление товара', 'Успешно', [
                            'id'      => $result->id,
                            'name'    => $result->name,
                            'article' => $result->sku
                        ]);
                    }
                }

                if ($images) {
                    //Удаляем фотографии
                    foreach ($images as $image) {
                        wp_delete_attachment($image, true);
                    }
                }


                return Natali_Handler::success('Удаление товаров', 'Успешно', [
                    'next'  => true,
                    'total' => Natali_Sync_Products::get_count(),
                ]);

            } else {
                return Natali_Handler::success('Удаление товара', 'Завершено', [
                    'next'    => false,
                    'total'   => Natali_Sync_Products::get_count(),
                    'message' => 'Все товары были удалены'
                ]);
            }
        } catch (Exception $error) {
            return Natali_Handler::error('Удаление товара', $error);
        }
    }

    public static function clearDb(WP_REST_Request $request)
    {
        try {
            $nataliList = new Natali_Model_ImportedProduct();
            $nataliList->deleteAll();

            return Natali_Handler::success('Удаление');

        } catch (Exception $error) {
            return Natali_Handler::error('Удаление', $error);
        }
    }
}
