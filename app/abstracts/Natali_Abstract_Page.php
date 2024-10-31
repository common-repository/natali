<?php

abstract class Natali_Abstract_Page
{
    private $data;

    public function __set($name, $value)
    {
        $this->data[$name] = $value;
    }

    public function &__get($name)
    {
        if (array_key_exists($name, $this->data)) {
            return $this->data[$name];
        }

        $trace = debug_backtrace();
        trigger_error(
            'Неопределённое свойство в __get(): ' . $name .
            ' в файле ' . $trace[0]['file'] .
            ' на строке ' . $trace[0]['line'],
            E_USER_NOTICE
        );
        return null;
    }

    public function __isset($name)
    {
        return isset($this->data[$name]);
    }

    public function __unset($name)
    {
        unset($this->data[$name]);
    }

    public function getData()
    {
        return $this->data;
    }

    public function render(array $params = [])
    {
        Natali_Plugin::update();
        if (isset($params['page'])) {
            $params['page'] = esc_attr($_GET['page']);
        }

        Natali_Template::the('layouts/app', $params);
    }
}
