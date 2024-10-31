<?php

class Natali_Add_Info_Single_Product
{
    public static function init()
    {
        $settings = new Natali_Model_Settings();

        $fields = [
            [
                'name'         => 'garments',
                'callback'     => 'get_size_table',
                'callback_tab' => 'create_tab_size_table'
            ],
            [
                'name'         => 'attributes',
                'callback'     => 'attributes_field',
                'callback_tab' => 'create_tab_size_table'
            ]
        ];

        foreach ($fields as $field) {
            $hook = $settings->get("{$field['name']}_place");
            $display = $settings->get("{$field['name']}_display");
            $priority = $settings->get("{$field['name']}_priority");

            $params = [
                'hook'     => $hook,
                'display'  => $display,
                'priority' => $priority,
            ];

            if ($hook === 'create_tab') {
                self::tab($params, ['Natali_Add_Info_Single_Product', $field['callback_tab']]);
            } else {
                self::field($params, ['Natali_Add_Info_Single_Product', $field['callback']]);
            }
        }

    }

    public static function tab($args, $callback)
    {
        $defaultArgs = [
            'display'  => 0,
            'priority' => 10,
        ];

        $params = array_merge($defaultArgs, $args);

        if ($params['display'] == 1) {
            add_action('woocommerce_product_tabs', $callback, $params['priority']);
        }
    }

    public static function field($args, $callback)
    {
        $defaultArgs = [
            'hook'     => null,
            'display'  => 0,
            'priority' => 10,
        ];

        $params = array_merge($defaultArgs, $args);

        if ($params['display'] == 1) {
            add_action($params['hook'], $callback, (int)$params['priority']);
        }
    }

    public static function getData()
    {
        global $post;
        $response = [];
        foreach (get_post_meta($post->ID, 'natali_data') as $k => $v) {
            $response = $v;
        }
        return $response;
    }

    public static function get_size_table()
    {
        global $post;
        $data = get_post_meta($post->ID, 'natali_data');

        Natali_Template::the('components/table/table-size', [
            'rows' => $data[0]['garments'] ?? null
        ]);
    }

    public static function create_tab_size_table($tabs)
    {
        $settings = new Natali_Model_Settings();

        $tabs['new_super_tab'] = array(
            'title'    => __('Таблица размеров', 'wp-natali'),
            'priority' => (int)$settings->get('garments_priority'),
            'callback' => ['Natali_Add_Info_Single_Product', 'tab_size_table_content']
        );

        return $tabs;
    }

    public static function tab_size_table_content()
    {
        $data = self::getData();

        Natali_Template::the('components/table/table-size', [
            'rows' => $data['garments'] ?? null
        ]);
    }

    public static function attributes_field()
    {
        $data = self::getData();
        $settings = new Natali_Model_Settings();

        Natali_Template::the('attributes/structure', [
            'data'     => $data ?? null,
            'settings' => $settings
        ]);
    }

}