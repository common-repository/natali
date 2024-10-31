<?php

class Natali_ProductList
{
    public $data;

    public function __construct() {

    }

    public function get() {
        $this->data = get_posts(
            [
                'post_type' => 'product',
                'tax_query' => [

                ]
            ]);
    }
}