<?php

/**
 * Class for create admin menu
 */
class Natali_Admin_Menu
{
    public $option  = 'manage_options';
    public $section = 'natali';

    public function __construct()
    {
        add_action('admin_menu', [$this, 'addMenu'], 1);
        add_action('admin_menu', [$this, 'removeMenu'], 9999);
    }

    public function addMenu()
    {
        add_menu_page(
            __('Каталог Натали', 'wp-natali'),
            __('Каталог Натали', 'wp-natali'),
            $this->option,
            $this->section,
            null,
            "data:image/svg+xml,%3Csvg width='20' height='20' viewBox='0 0 20 20' fill='none' xmlns='http://www.w3.org/2000/svg'%3E%3Cpath fill-rule='evenodd' clip-rule='evenodd' d='M12.5864 0.212818C12.5273 0.0806501 12.3932 0 12.2459 0L11.8265 0C11.6152 0 11.4406 0.164823 11.4176 0.370702C11.3264 1.1857 10.958 3.06139 9.49825 3.06139C8.03846 3.06139 7.67009 1.1857 7.57893 0.370702C7.5559 0.164824 7.38131 0 7.16998 0H6.84071C6.71495 0 6.59758 0.0588293 6.53326 0.164741C6.27518 0.589692 5.64616 1.80538 5.73102 3.44407C5.81782 5.12028 6.6277 6.77844 6.92299 7.33222C6.98756 7.45332 7.11597 7.52593 7.25539 7.52593H11.7224C11.8717 7.52593 12.0073 7.44323 12.0692 7.3101C12.3593 6.68629 13.1693 4.83673 13.2655 3.44407C13.3583 2.09962 12.8171 0.728097 12.5864 0.212818Z' fill='white'/%3E%3Cpath d='M5.21 11.0815C5.21848 11.059 5.22704 11.0367 5.23568 11.0144C5.88944 9.3295 7.69269 8.53333 9.5 8.53333V8.53333V8.53333C11.3073 8.53333 13.1106 9.3295 13.7643 11.0144C13.773 11.0367 13.7815 11.059 13.79 11.0815C14.7148 13.5288 15.4423 16.8144 15.7852 18.5068C15.9263 19.2037 15.6711 20 14.96 20V20C14.18 20 14.2434 19.6388 13.27 19.6178C12.2966 19.5968 12.4833 20 11.45 20C10.61 20 10.6877 19.6178 9.5 19.6178C8.31227 19.6178 8.39002 20 7.55 20C6.51673 20 6.70337 19.5968 5.73 19.6178C4.75663 19.6388 4.82 20 4.04 20V20C3.32894 20 3.07368 19.2037 3.21485 18.5068C3.55769 16.8144 4.28515 13.5288 5.21 11.0815Z' fill='white'/%3E%3C/svg%3E%0A",
            56
        );

        add_submenu_page(
            $this->section,
            __('Импорт товаров', 'wp-natali'),
            __('Импорт товаров', 'wp-natali'),
            $this->option,
            'natali_import',
            [new Natali_Page_Import, 'render']
        );

        add_submenu_page(
            $this->section,
            __('Экспорт товаров', 'wp-natali'),
            __('Экспорт товаров', 'wp-natali'),
            $this->option,
            'natali_export',
            [new Natali_Page_Export, 'render']
        );

        add_submenu_page(
            $this->section,
            __('Синхронизация товаров', 'wp-natali'),
            __('Синхронизация', 'wp-natali'),
            $this->option,
            'natali_sync',
            [new Natali_Page_Sync, 'render']
        );


        add_submenu_page(
            $this->section,
            __('Настройки', 'wp-natali'),
            __('Настройки', 'wp-natali'),
            $this->option,
            'natali_settings',
            [new Natali_Page_Settings, 'render']
        );

        add_submenu_page(
            $this->section,
            __('Удаление товаров', 'wp-natali'),
            __('Удаление товаров', 'wp-natali'),
            $this->option,
            'natali_delete',
            [new Natali_Page_Delete, 'render']
        );

        add_submenu_page(
            $this->section,
            __('Поддержка', 'wp-natali'),
            __('Поддержка', 'wp-natali'),
            $this->option,
            'natali_support',
            [new Natali_Page_Support, 'render']
        );
    }

    public function removeMenu()
    {
        remove_submenu_page($this->section, $this->section);
    }
}