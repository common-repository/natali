<?php

class Natali_Tab_Data
{
    public static $id = "natali_data";
    public static $data;

    public function __construct() {

    }

    public static function tab_admin($tab) {
        global $post;
        $data = get_post_meta($post->ID, 'natali_data');

        if (isset($data[0])) {
            $tab[self::$id] = [
                'label'  => "Информация товара натали",
                'target' => self::$id
            ];

            return $tab;
        }

        return $tab;
    }

    public static function tab_admin_content($id) {
        global $post;
        $data = get_post_meta($post->ID, 'natali_data');

        Natali_Template::the('tabs/tab-data', [
            'id'   => self::$id,
            'post' => $post,
            'data' => $data[0] ?? null
        ]);
    }
}