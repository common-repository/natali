<?php

class Natali_Api_WooCommerce extends \Automattic\WooCommerce\Client
{
    protected static $_keys;

    protected function get_keys()
    {
        $dbSettings = new Natali_Model_Settings();
        $user_key = $dbSettings->get('user_key');
        $secret_key = $dbSettings->get('secret_key');

        return [
            'user_key'   => $user_key,
            'secret_key' => $secret_key,
        ];
    }


    function __construct($options = [])
    {
        $keys = $this->get_keys();

        //Установить опции по умолчанию
        $defaultOptions = [
            'timeout' => 0,
        ];

        $resultOptions = array_merge($defaultOptions, $options);

        parent::__construct(get_home_url(), $keys['user_key'], $keys['secret_key'], $resultOptions);
    }
}
