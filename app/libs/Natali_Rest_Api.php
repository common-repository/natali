<?php

class Natali_Rest_Api
{
    public static $namespace = 'natali/v2';

    public static $default_params = [
        'methods'  => 'GET',
        'callback' => null,
        'permission_callback' => '__return_true',
    ];

    public static function create_point($rout, $params)
    {
        $params = array_merge(self::$default_params, $params);

        add_action( 'rest_api_init', function() use ($params, $rout) {
            register_rest_route( self::$namespace, $rout, $params);
        });
    }
}