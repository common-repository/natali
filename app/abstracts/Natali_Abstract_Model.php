<?php

abstract class Natali_Abstract_Model
{
    public $name;
    public $db;
    protected $queryCreate;
    protected $queryDrop;
    public static $_data;

    public function __construct()
    {
        global $wpdb;
        $wpdb->hide_errors();
        $this->db = $wpdb;
        $this->queryDrop = "DROP TABLE IF EXISTS `{$this->name}`";
    }

    public function getTableName()
    {
        return $this->name;
    }

    public function getAllRows($limit = 0)
    {
        if ($limit) {
            return $this->db->get_results("SELECT * FROM `{$this->name}` LIMIT {$limit}", ARRAY_A);
        } else {
            return $this->db->get_results("SELECT * FROM `{$this->name}`", ARRAY_A);
        }
    }

    public function getRowById($id)
    {
        return $this->db->get_row("SELECT * FROM `{$this->name}` WHERE id = {$id}", ARRAY_A);
    }

    public function getRowByField($key, $value)
    {
        return $this->db->get_row("SELECT * FROM `{$this->name}` WHERE `{$key}` = '{$value}'", ARRAY_A);
    }

    public function getRows(int $limit = 10)
    {
        if ($limit) {
            return $this->db->get_results("SELECT * FROM `{$this->name}` LIMIT {$limit}", ARRAY_A);
        }

        return $this->db->get_results("SELECT * FROM `{$this->name}`", ARRAY_A);
    }

    public function getRowsFilter($params, $limit = 10)
    {
        $counter = count($params);
        $filter = '';
        $index = 1;
        foreach ($params as $key => $value) {
            $filter .= "`{$key}` = '{$value}'";

            if ($index < $counter) {
                $filter .= ' AND ';
            }
            $index++;
        }

        if ($limit == 0) {
            return $this->db->get_results("SELECT * FROM `{$this->name}` WHERE {$filter}", ARRAY_A);
        } else {
            return $this->db->get_results("SELECT * FROM `{$this->name}` WHERE {$filter} LIMIT {$limit}", ARRAY_A);
        }
    }

    public function create()
    {
        $this->db->query($this->queryCreate);
    }

    public function drop()
    {
        $this->db->query($this->queryDrop);
    }

    public function update($data, $where)
    {
        $this->db->update($this->name, $data, $where);
    }

    public function delete($array)
    {
        $this->db->delete($this->name, $array);
    }

    public function insert($array)
    {
        $this->db->insert($this->name, $array);
    }

    public function allClear()
    {
        $this->db->query("DELETE FROM `{$this->name}`");
    }

    public function query($query)
    {
        $this->db->query($query);
    }
}
