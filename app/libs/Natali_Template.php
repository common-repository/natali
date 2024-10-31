<?php

/**
 * Class Template
 */
class Natali_Template
{
    public static function template($file, $args = [], $ext = 'php')
    {
        if (!empty($args)) {
            extract($args, EXTR_OVERWRITE);
        }

        $filePath = preg_replace('|([/]+)|s', '/', NATALI_PLUGIN_DIR . '/app/views/' . $file . '.' . $ext);

        if (file_exists($filePath)) {
            include $filePath;
        } else {
            print_r("Файл - $filePath не найден \n");
        }
    }

    public static function the($file, $args = [], $ext = 'php')
    {
        ob_start();
        self::template($file, $args, $ext);
        echo ob_get_clean();
    }

    public static function get($file, $args = [], $ext = 'php')
    {
        ob_start();
        self::template($file, $args, $ext);
        return ob_get_clean();
    }
}
