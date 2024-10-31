<?php

class Natali_Model_Attributes extends Natali_Abstract_Model
{
    public        $_list;
    public        $list;
    public static $store;

    function __construct() {
        parent::__construct();

        $this->name = $this->db->prefix . 'natali_attributes';

        $this->queryCreate = "CREATE TABLE IF NOT EXISTS `{$this->name}`(
            `ID` int(0) NOT NULL,
            `name` TEXT NOT NULL,
            `slug` VARCHAR(200) NOT NULL,
            `has_archives` int(0) NOT NULL,
            PRIMARY KEY (`ID`)
            )";

        $this->_list = [
            [
                'name'         => __('Товар натали', 'wp-natali'),
                'slug'         => 'natali_is_natali',
                'has_archives' => 0,
            ],
            [
                'name'         => __('Материал', 'wp-natali'),
                'slug'         => 'natali_materials',
                'has_archives' => 0,
            ],
            [
                'name'         => __('Бренд', 'wp-natali'),
                'slug'         => 'natali_brands',
                'has_archives' => 0,
            ],
            [
                'name'         => __('Состав', 'wp-natali'),
                'slug'         => 'natali_composition',
                'has_archives' => 0,
            ],
            [
                'name'         => __('Натали артикул', 'wp-natali'),
                'slug'         => 'natali_id',
                'has_archives' => 0,
            ],
            [
                'name'         => __('Цвет', 'wp-natali'),
                'slug'         => 'natali_color',
                'has_archives' => 0,
            ],
            [
                'name'         => __('Товар маркирован', 'wp-natali'),
                'slug'         => 'natali_is_marked',
                'has_archives' => 0,
            ],
            [
                'name'         => __('Размер', 'wp-natali'),
                'slug'         => 'natali_size',
                'has_archives' => 1,
            ],
            [
                'name'         => __('Лейбл', 'wp-natali'),
                'slug'         => 'natali_labels',
                'has_archives' => 1,
            ],
            [
                'name'         => __('Мин. размер', 'wp-natali'),
                'slug'         => 'natali_minsize',
                'has_archives' => 0,
            ],
            [
                'name'         => __('Макс. размер', 'wp-natali'),
                'slug'         => 'natali_maxsize',
                'has_archives' => 0,
            ],
            [
                'name'         => __('Видео', 'wp-natali'),
                'slug'         => 'natali_video',
                'has_archives' => 0,
            ]
        ];

        $this->initStore();
    }

    public function createAll($create_new = false) {
        //Получение уже созданных атрибутов
        $attr_terms = wc_get_attribute_taxonomies();
        $attr_terms = json_decode(json_encode($attr_terms), true);

        foreach ($this->_list as $attribute) {
            //Проверка на существование нужных для натали атрибутов
            $check = array_filter($attr_terms, function ($value) use ($attribute) {
                return $value['attribute_name'] === $attribute['slug'];
            });

            if ($check) {
                $current = current($check);

                if (isset($current['attribute_id'])) {
                    $attribute['id'] = $current['attribute_id'];
                } else {
                    $attribute['id'] = wc_create_attribute($attribute);
                }

            } else {
                $attribute['id'] = wc_create_attribute($attribute);
            }

            $this->db->insert($this->name, $attribute);
        }

    }

    public function initStore() {
        $array = $this->getAllRows();
        foreach ($array as $item) {
            self::$store[$item['slug']] = ['id' => $item['ID'], 'name' => $item['name']];
        }
    }

    public function get_data() {
        return self::$store;
    }

    public function getIdBySlug($slug) {
        if (!isset(self::$store[$slug]['id'])) {
            $this->createAll(true);
            $this->initStore();
        }

        return self::$store[$slug]['id'];
    }
}
