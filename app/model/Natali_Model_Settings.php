<?php

class Natali_Model_Settings extends Natali_Abstract_Model
{
    protected $defaultSettings = [
        'user_key'             => '',
        'secret_key'           => '',
        'labels'               => 'Y',
        'materials'            => 'Y',
        'natali_brands'        => 'Y',
        'composition'          => 'Y',
        'video'                => 'Y',
        'minSize'              => 'Y',
        'maxSize'              => 'Y',
        'isMarked'             => 'Y',
        'product_type'         => 'variable',   // variable | simple | grouped | external
        'price_type'           => 'price',      // price | priceWholesale | priceSmallWholesale
        'images'               => 'previewUrl', // previewUrl | url
        'image_color'          => 0, // previewUrl | url
        'regular_price_margin' => 0,
        'sale_price_margin'    => 0,
        'importRepeat'         => 0,
        'is_nl_attributes'     => 0,
        'garments_display'     => 0,
        'garments_priority'    => 10,
        'garments_place'       => 'woocommerce_product_additional_information',
        'is_importing'         => 0,
        'attributes_display'   => 0,
        'attributes_priority'  => 10,
        'attributes_place'     => 'woocommerce_product_additional_information',
    ];

    public static $store;
    public static $data;

    function __construct()
    {
        global $wpdb;
        $this->name = $wpdb->prefix . 'natali_settings';
        $this->db = $wpdb;

        $this->queryCreate = "CREATE TABLE IF NOT EXISTS `{$this->name}`(
            `ID` int(0) NOT NULL AUTO_INCREMENT,
            `KEY` VARCHAR(200) NOT NULL,
            `VALUE` text NOT NULL,
            PRIMARY KEY (`ID`),
            UNIQUE (`KEY`)
            )";

        parent::__construct();
        $this->initStore();
    }

    private function initStore()
    {
        $array = $this->getAllRows();

        foreach ($array as $item) {
            self::$store[$item['KEY']] = $item['VALUE'];
        }
    }

    public function getAll()
    {
        return self::$store;
    }

    public function save($params, $replace = true)
    {
        $data = array_merge($this->defaultSettings, $params);

        foreach ($params as $key => $item) {
            $changer = $this->getRowByField('KEY', $key);

            if (is_array($changer) && $replace) {
                $this->db->update($this->name, ['value' => sanitize_text_field($item)], ['key' => $key]);
            } else if (!is_array($changer)) {
                $this->db->insert($this->name, ['key' => $key, 'value' => sanitize_text_field($item)]);
            }
        }
        self::$store = array_merge(self::$store, $params);
        $this->initStore();
    }

    public function set($key, $value)
    {
        if (!isset(self::$store[$key])) {
            $this->db->insert($this->name, ['key' => $key, 'value' => 0]);
        } else {
            $this->db->update($this->name, ['value' => $value], ['key' => $key]);
        }
        $this->initStore();
        self::$store[$key] = $value;
    }

    public function get($key)
    {

        if (!isset(self::$store[$key])) {
            $this->set($key, 0);
        }

        return self::$store[$key];
    }

    public function reSave()
    {
        $data = $this->getAll();
        $newData = array_merge($this->defaultSettings, $data);
        $this->save($newData);
    }

    public function setDefault()
    {
        $this->save($this->defaultSettings, false);
    }
}
