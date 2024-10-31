<?php

class Natali_Sync_Products
{
    public static function get_list($args = [])
    {
        $wc = new Natali_Api_WooCommerce();
        $attrs = wc_get_attribute_taxonomies();
        $is_natali = array_filter($attrs, function ($attr) {
            return $attr->attribute_name === 'natali_is_natali';
        });

        foreach ($is_natali as $key => $item) {
            $terms = get_terms(
                [
                    'taxonomy'   => ['pa_natali_is_natali'],
                    'hide_empty' => false,
                ]);
        }

        $defaultArgs = [
            'per_page'       => 1,
            'attribute'      => 'pa_natali_is_natali',
            'attribute_term' => $terms[0]->term_id ?? null
        ];

        $params = array_merge($defaultArgs, $args);

        return $wc->get('products', $params);
    }

    public static function get_all_data()
    {
        $data = self::get_list();

        return [
            'total'    => self::get_count(),
            'products' => $data
        ];
    }

    public static function filter_is_no_exist($array_new_products)
    {
        $data = self::get_list();
    }

    public static function get_count()
    {
        $counter = 0;
        $terms = get_terms(
            [
                'taxonomy'   => 'pa_natali_is_natali',
                'hide_empty' => false,
            ]);

        foreach ($terms as $term) {
            if ($term->name === "Y") {
                $counter = $term->count;
            }
        }

        return $counter;
    }
}