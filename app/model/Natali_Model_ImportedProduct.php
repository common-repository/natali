<?php

class Natali_Model_ImportedProduct extends Natali_Abstract_Model
{
    public $update;
    public function __construct()
    {
        global $wpdb;
        $this->name = $wpdb->prefix . 'natali_imported_products';

        $this->queryCreate = "CREATE TABLE IF NOT EXISTS `{$this->name}`(
            `ID` int(0) NOT NULL AUTO_INCREMENT,
            `product_id` int(0) NOT NULL,
            `wc_id` int(0) NOT NULL,
            `wc_category_id` int(0) NOT NULL,
            `remove_trash` int(0) NOT NULL DEFAULT 0,
            `create_date` datetime DEFAULT CURRENT_TIMESTAMP,
            `last_modify` datetime DEFAULT CURRENT_TIMESTAMP,
            PRIMARY KEY (`ID`)
            )";

        parent::__construct();
    }

    public function insert($data) {
        try {
            $find = $this->db->get_row("SELECT * FROM `{$this->name}` WHERE product_id = {$data['product_id']}", ARRAY_A);
            if (!$find){
                $this->db->insert($this->name, $data);
            }
        } catch (Exception $error) {
            $id = $data['wc_id'] ?? $data['product_id'];
            Natali_Log::set($error->getMessage(), $error->getCode());
        }
    }


    public function counterAll()
    {
        return count($this->getAllRows());
    }

    public function get($limit){
        return $this->getRows($limit);
    }

    public function getById($id){
        return $this->getRowByField('product_id', $id);
    }

    public function deleteAll()
    {
        $list = $this->getAllRows();
        foreach ($list as $item) {
            parent::delete($item);
        }
    }

}