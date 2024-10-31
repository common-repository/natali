<?php

/**
 * The Main class for Plugin
 */
class Natali_Plugin
{

    public static $file = NATALI_PLUGIN_FILE;

    public static $admin_menu;

    /**
     * Method for activation plugin
     */
    public static function activate() {
        try {
            $settings = new Natali_Model_Settings();
            $settings->create();
            $settings->setDefault();

            $attributes = new Natali_Model_Attributes();
            $attributes->create();  //Создание таблицы атрибутов
            $attributes->createAll(); // Создание самих атрибутов

            $tempImport = new Natali_Model_ImportTemp();
            $tempImport->create();

            $categories = new Natali_Model_Categories();
            $categories->create();

            $importedProducts = new Natali_Model_ImportedProduct();
            $importedProducts->create();

        } catch (\Exception $error) {
            Natali_Log::set($error->getMessage(), $error->getCode());
        }
    }

    /**
     * Method for deactivation plugin
     */
    public static function deactivate() {
    }

    public static function require_classes() {
        $paths = [
            'abstracts/Natali_Abstract_Model',     // Абстрактный класс модели базы данных
            'abstracts/Natali_Abstract_Page',  // Абстрактный класс для создания страниц
            'libs/Natali_Log',
            'libs/Natali_Sync_Products',
            'libs/Natali_Admin_Menu',
            'libs/Natali_Api_DataBase',
            'libs/Natali_Rest_Api',
            'libs/Natali_Api_WooCommerce',
            'libs/Natali_Files',
            'libs/Natali_Product_New',
            'libs/Natali_Handler',
            'libs/Natali_Tab_Data',
            'libs/Natali_Add_Info_Single_Product',
            'libs/Natali_Template',
            'pages/Natali_Page_Delete',
            'pages/Natali_Page_Export',
            'pages/Natali_Page_Import',
            'pages/Natali_Page_Settings',
            'pages/Natali_Page_Support',
            'pages/Natali_Page_Sync',
            'model/Natali_Model_Attributes',       // Модель для работы с таблицей настроек
            'model/Natali_Model_Categories',       // Модель для работы с таблицей временных данных для импорта
            'model/Natali_Model_ImportedProduct',  // Модель для работы с таблицей атрибутов
            'model/Natali_Model_ImportTemp',       // Модель для работы с Таблицей импортированных товаров
            'model/Natali_Model_Settings',         // Модель для работы с таблицей атрибутов
        ];

        foreach ($paths as $item) {
            include NATALI_PLUGIN_DIR . '/app/' . $item . '.php';
        }
    }

    public static function uninstall() {
        global $wpdb;

        $settings = new Natali_Model_Settings();
        $settings->drop();

        $attributes = new Natali_Model_Attributes();
        $attributes->drop();

        $tempImport = new Natali_Model_ImportTemp();
        $tempImport->drop();

        $categories = new Natali_Model_Categories();
        $categories->drop();

        $importedProducts = new Natali_Model_ImportedProduct();
        $importedProducts->drop();
    }

    public static function assets($path) {
        return NATALI_PLUGIN_URL . '/dist/' . $path;
    }

    public static function init_points_rest_api() {
        Natali_Rest_Api::create_point('sync', [
            'callback' => ['Natali_Page_Sync', 'get_sync_data']
        ]);

        Natali_Rest_Api::create_point('startCron', [
            'methods'  => 'GET',
            'callback' => ['Natali_Page_Sync', 'start_sync_cron']
        ]);

        Natali_Rest_Api::create_point('startSync', [
            'methods'  => 'GET',
            'callback' => ['Natali_Page_Sync', 'start_sync']
        ]);

        Natali_Rest_Api::create_point('send_message', [
            'methods'  => 'GET',
            'callback' => ['Natali_Page_Support', 'send_message']
        ]);

        Natali_Rest_Api::create_point('get_delete_data', [
            'methods'  => 'GET',
            'callback' => ['Natali_Page_Delete', 'get_data']
        ]);

        Natali_Rest_Api::create_point('delete_start', [
            'methods'  => 'DELETE',
            'callback' => ['Natali_Page_Delete', 'start']
        ]);

        Natali_Rest_Api::create_point('clearImportDb', [
            'methods'  => 'DELETE',
            'callback' => ['Natali_Page_Delete', 'clearDb']
        ]);

        Natali_Rest_Api::create_point('reUpdateProducts', [
            'methods'  => 'POST',
            'callback' => ['Natali_Page_Sync', 're_update_products']
        ]);

        Natali_Rest_Api::create_point('syncImportProduct', [
            'methods'  => 'POST',
            'callback' => ['Natali_Page_Sync', 'import_product']
        ]);

        Natali_Rest_Api::create_point('syncUpdateProduct', [
            'methods'  => 'POST',
            'callback' => ['Natali_Page_Sync', 'update_product']
        ]);

        Natali_Rest_Api::create_point('syncDeleteProduct', [
            'methods'  => 'DELETE',
            'callback' => ['Natali_Page_Sync', 'delete_product']
        ]);

        Natali_Rest_Api::create_point('repairProducts', [
            'methods'  => 'POST',
            'callback' => ['Natali_Page_Sync', 'repair_product']
        ]);

        Natali_Rest_Api::create_point('repairInfo', [
            'methods'  => 'POST',
            'callback' => ['Natali_Page_Sync', 'repair_info']
        ]);
    }

    public static function init_hooks() {
        $settings = new Natali_Model_Settings();

        add_action('admin_head', ['Natali_Plugin', 'enqueue_admin_head'], 11); //Подключаем стили плагина
        add_action('admin_footer', ['Natali_Plugin', 'enqueue_admin_footer'], 11); //Подключаем скрипты плагина

        add_action('wp_enqueue_scripts', ['Natali_Plugin', 'enqueue_front_script'], 11);

        add_action('wp_ajax_natali_save_settings', ['Natali_Page_Settings', 'saveSettings']);
        add_action('wp_ajax_nopriv_natali_save_settings', ['Natali_Page_Settings', 'saveSettings']);

        add_action('wp_ajax_natali_save_import_settings', ['Natali_Page_Import', 'ajaxSaveImportSettings']);
        add_action('wp_ajax_nopriv_natali_save_import_settings', ['Natali_Page_Import', 'ajaxSaveImportSettings']);

        add_action('wp_ajax_natali_get_status_importing', ['Natali_Page_Import', 'getStatusImporting']);
        add_action('wp_ajax_nopriv_natali_get_status_importing', ['Natali_Page_Import', 'getStatusImporting']);

        add_action('wp_ajax_natali_update', ['Natali_Plugin', 'update']);
        add_action('wp_ajax_nopriv_natali_update', ['Natali_Plugin', 'update']);

        add_action('wp_ajax_natali_start_export', ['Natali_Page_Export', 'startExport']);
        add_action('wp_ajax_nopriv_natali_start_export', ['Natali_Page_Export', 'startExport']);

        add_action('wp_ajax_natali_start_import', ['Natali_Plugin', 'start_cron']);
        add_action('wp_ajax_nopriv_natali_start_import', ['Natali_Plugin', 'start_cron']);

        add_action('wp_ajax_natali_delete_products', ['Natali_Page_Delete', 'get_data']);
        add_action('wp_ajax_nopriv_natali_delete_products', ['Natali_Page_Delete', 'get_data']);

        add_action('woocommerce_product_data_tabs', ['Natali_Tab_Data', 'tab_admin']);
        add_action('woocommerce_product_data_panels', ['Natali_Tab_Data', 'tab_admin_content']);

        add_filter('plugin_action_links', ['Natali_Plugin', 'settingsLink'], 10, 4);


        self::generate_cron_file();

        Natali_Add_Info_Single_Product::init();

        self::$admin_menu = new Natali_Admin_Menu();

        // add_action( 'upgrader_process_complete', ['Natali_Plugin', 'upgrade_plugin'], 10, 2 );
    }

    public static function plugin_settings_link($links) {
        array_unshift($links, '<a href="admin.php?page=natali_settings">Настройки</a>');
        return $links;
    }

    public static function update()
    {
        $update = false;
        if (self::versionUp(get_option('natali_update'), '2.1.4')) {
            $attributes = new Natali_Model_Attributes();
            $attributes->createAll(); // Создание самих атрибутов
            update_option('natali_update', '2.1.4');
            $update = true;
        }

        if (self::versionUp(get_option('natali_update'), '2.1.5')) {
            $update = true;
            update_option('natali_update', '2.1.5');
        }

        if ($update) {
            $settings = new Natali_Model_Settings();
            $settings->reSave();
        }
    }

    public static function versionUp($current, $new)
    {
        $current = preg_replace('/[^0-9]/', '', $current);
        $new = preg_replace('/[^0-9]/', '', $new);

        return $current < $new;
    }

    public static function start_cron() {
        set_time_limit(0);
        $time = microtime(true);
        try {
            $productList = new Natali_Model_ImportTemp();

            $counter = count($productList->getNotExistProducts(0));
            if ($counter > 0) {
                $newProduct = Natali_Page_Import::start();

                if ($newProduct) {
                    echo json_encode(Natali_Handler::success('Импорт товара', 'Успешно выполнен', [
                        'isImporting' => $counter,
                        'natali_id'   => $newProduct->get_natali_id(),
                        'wc_id'       => $newProduct->get_wc_id(),
                    ],                                       round(microtime(true) - $time, 4)));
                }
            }


        } catch (Exception $error) {
            Natali_Log::set($error->getMessage() . ' - ' . $error->getFile(), $error->getCode());
            echo json_encode(
                [
                    'status'  => $error->getCode(),
                    'message' => $error->getMessage(),
                    'trace'   => $error->getTrace(),
                    'line'    => $error->getLine(),
                    'file'    => $error->getFile(),
                    'time'    => round(microtime(true) - $time, 4)
                ]
            );
        }

        wp_die();
    }

    public static function enqueue_admin_footer() {
        wp_enqueue_script('natali-admin', self::assets('nataliAdmin.js'), ['wp-api'], NATALI_PLUGIN_VERSION);
    }

    public static function enqueue_admin_head() {
        wp_enqueue_script('natali-vue', 'https://unpkg.com/vue@3.2.31/dist/vue.global.prod.js', null, null, false);
        wp_enqueue_style('wp-natali', self::assets('nataliAdmin.css'), false, NATALI_PLUGIN_VERSION);
    }

    public static function enqueue_front_script() {
        wp_enqueue_style('natali-style-front', self::assets('nataliFront.css'), false, NATALI_PLUGIN_VERSION);
        wp_enqueue_script('natali-admin', self::assets('nataliFront.js'), null, NATALI_PLUGIN_VERSION);
    }

    public static function settingsLink($actions, $pluginFile) {
        if (strpos($pluginFile, 'natali') === false) {
            return $actions;
        }

        $settingsLink = '<a href="admin.php?page=natali_settings">' . __('Настройки', 'wp-natali') . '</a>';
        array_unshift($actions, $settingsLink);

        return $actions;
    }


    public static function generate_cron_file() {
        $site_url = get_home_url();
        $file_name = '/natali_cron.php';
        $file_content = "
<?php
\$site = '$site_url';  // Здесь должен быть адрес вашего сайта

\$url =  preg_replace('|([/]+)|s', '/', \$site . '/wp-json/natali/v2/startCron');
\$ch = curl_init(\$url);

curl_setopt(\$ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt(\$ch, CURLOPT_POST, true);

\$response = curl_exec(\$ch);
curl_close(\$ch);";

        $file = fopen(NATALI_PLUGIN_DIR . $file_name, "w"); //поэтому используем режим 'w'

        // записываем данные в открытый файл
        fwrite($file, $file_content);

        //не забываем закрыть файл, это ВАЖНО
        fclose($file);
    }
}
