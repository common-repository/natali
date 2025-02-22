<?php

// autoload_static.php @generated by Composer

namespace Composer\Autoload;

class ComposerStaticInit9e08d8daccd2ea7cfbc4ffa43a25b797
{
    public static $prefixLengthsPsr4 = array (
        'D' => 
        array (
            'Drozzi\\NataliProducts\\' => 22,
        ),
        'A' => 
        array (
            'Automattic\\WooCommerce\\' => 23,
        ),
    );

    public static $prefixDirsPsr4 = array (
        'Drozzi\\NataliProducts\\' => 
        array (
            0 => __DIR__ . '/../..' . '/resources',
        ),
        'Automattic\\WooCommerce\\' => 
        array (
            0 => __DIR__ . '/..' . '/automattic/woocommerce/src/WooCommerce',
        ),
    );

    public static $classMap = array (
        'Composer\\InstalledVersions' => __DIR__ . '/..' . '/composer/InstalledVersions.php',
    );

    public static function getInitializer(ClassLoader $loader)
    {
        return \Closure::bind(function () use ($loader) {
            $loader->prefixLengthsPsr4 = ComposerStaticInit9e08d8daccd2ea7cfbc4ffa43a25b797::$prefixLengthsPsr4;
            $loader->prefixDirsPsr4 = ComposerStaticInit9e08d8daccd2ea7cfbc4ffa43a25b797::$prefixDirsPsr4;
            $loader->classMap = ComposerStaticInit9e08d8daccd2ea7cfbc4ffa43a25b797::$classMap;

        }, null, ClassLoader::class);
    }
}
