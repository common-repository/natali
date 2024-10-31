<?php

class Natali_Model_ImportTemp extends Natali_Abstract_Model
{
    public function __construct()
    {
        global $wpdb;
        $this->name = $wpdb->prefix . 'natali_import_temp';

        $this->queryCreate = "CREATE TABLE IF NOT EXISTS `{$this->name}`(
            `ID` int(0) NOT NULL AUTO_INCREMENT,
            `product_id` int(0) NOT NULL,
            `natali_category_id` int(0) NOT NULL,
            `wc_category_id` int(0) NOT NULL,
            `status` int NOT NULL default 0,
            `replace` int NOT NULL default 0,
            PRIMARY KEY (`ID`)
            )";

        parent::__construct();
    }

    public function getNotExistProducts($limit = 10)
    {
        if ($limit !== 0) {
            return $this->db->get_results("SELECT * FROM {$this->name} WHERE status = 0 LIMIT {$limit}", ARRAY_A);
        } else {
            return $this->db->get_results("SELECT * FROM {$this->name} WHERE status = 0", ARRAY_A);
        }
    }

    public function setProductStatus($product_id, $status)
    {
        $this->db->update($this->name, ['status' => $status], ['product_id' => $product_id]);
    }

    public function reset()
    {
        $this->allClear();
    }

    public function insert($data)
    {
        $find = $this->db->get_row(
            "SELECT * FROM `{$this->name}` WHERE product_id = " . $data['product_id'],
            ARRAY_A
        );

        if (!$find && $data) {
            $this->db->insert($this->name, $data);
        }
    }

    public function delete($id)
    {
        $this->db->delete($this->name, ['product_id' => $id]);
    }
}
