<?php

class Natali_Log
{
    protected static $row = "[%1s] [%2s] - %3s" . PHP_EOL;
    protected static $path;

    public static function set($message, $code = 0, $type = 'log')
    {
        self::$path = NATALI_PLUGIN_DIR . '/log/';

        if (!file_exists(self::$path)) {
            mkdir(self::$path);
        }
        $file = self::$path . '/' . $type;

        if (is_string($message)) {
            file_put_contents($file, self::createRow($message, $code), FILE_APPEND | LOCK_EX);
        }
    }

    private static function createRow($message, $code = 0)
    {
        $string = '[' . date('d.m.Y H:i:s') . ']';
        $string.= $code ? "[$code]" : null;
        $string.= " - $message";
        $string.= PHP_EOL;

        return $string;
    }

    public static function setArray($array, $event = null)
    {
        if ($event) {
            self::set("$event");
        }

        foreach ($array as $key => $value) {
            self::set("[{$key}] => {$value}", 'data');

            if (is_array($value)) {
                foreach ($value as $k => $v) {
                    self::set("=> [{$k}] => {$v}", 'data');
                }
            }
        }
    }
}
