<?php

class Natali_Model_Categories extends Natali_Abstract_Model
{
    public function __construct()
    {
        global $wpdb;
        $this->name = $wpdb->prefix . 'natali_import_categories';

        $this->queryCreate = "CREATE TABLE IF NOT EXISTS `{$this->name}`(
            `ID` int(0) NOT NULL AUTO_INCREMENT,
            `natali_id` int(0) NOT NULL,
            `wc_id` int(0) NOT NULL,
            PRIMARY KEY (`ID`))";

        parent::__construct();
    }

    public function save($data){

        foreach ($data as $nataliId => $wcId) {
            $checker = $this->db->get_row(
                "SELECT * FROM " . $this->name . " WHERE `natali_id` =" . $nataliId,
                ARRAY_A
            );

            if (is_null($checker)) {
                $this->db->insert($this->name, ['natali_id' => $nataliId, 'wc_id' => $wcId], ['%d', '%d']);
            } else {
                if ($checker['wc_id'] !== $wcId) {
                    $this->db->update(
                        $this->name,
                        ['natali_id' => $nataliId, 'wc_id' => $wcId],
                        ['natali_id' => $nataliId]
                    );
                }
            }
        }

    }
}
