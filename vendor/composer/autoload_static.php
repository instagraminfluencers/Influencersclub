<?php

// autoload_static.php @generated by Composer

namespace Composer\Autoload;

class ComposerStaticInit1d30e53b5e7941a246e6b2b4cc26c87c
{
    public static $files = array (
        '320cde22f66dd4f5d3fd621d3e88b98f' => __DIR__ . '/..' . '/symfony/polyfill-ctype/bootstrap.php',
    );

    public static $prefixLengthsPsr4 = array (
        'S' => 
        array (
            'Symfony\\Polyfill\\Ctype\\' => 23,
        ),
        'R' => 
        array (
            'Ramsey\\Uuid\\' => 12,
        ),
        'P' => 
        array (
            'Parse\\' => 6,
        ),
        'I' => 
        array (
            'Instagram\\' => 10,
        ),
        'C' => 
        array (
            'Curl\\' => 5,
        ),
    );

    public static $prefixDirsPsr4 = array (
        'Symfony\\Polyfill\\Ctype\\' => 
        array (
            0 => __DIR__ . '/..' . '/symfony/polyfill-ctype',
        ),
        'Ramsey\\Uuid\\' => 
        array (
            0 => __DIR__ . '/..' . '/ramsey/uuid/src',
        ),
        'Parse\\' => 
        array (
            0 => __DIR__ . '/..' . '/parse/php-sdk/src/Parse',
        ),
        'Instagram\\' => 
        array (
            0 => __DIR__ . '/../..' . '/src',
        ),
        'Curl\\' => 
        array (
            0 => __DIR__ . '/..' . '/php-curl-class/php-curl-class/src/Curl',
        ),
    );

    public static $prefixesPsr0 = array (
        'J' => 
        array (
            'JsonMapper' => 
            array (
                0 => __DIR__ . '/..' . '/netresearch/jsonmapper/src',
            ),
        ),
    );

    public static function getInitializer(ClassLoader $loader)
    {
        return \Closure::bind(function () use ($loader) {
            $loader->prefixLengthsPsr4 = ComposerStaticInit1d30e53b5e7941a246e6b2b4cc26c87c::$prefixLengthsPsr4;
            $loader->prefixDirsPsr4 = ComposerStaticInit1d30e53b5e7941a246e6b2b4cc26c87c::$prefixDirsPsr4;
            $loader->prefixesPsr0 = ComposerStaticInit1d30e53b5e7941a246e6b2b4cc26c87c::$prefixesPsr0;

        }, null, ClassLoader::class);
    }
}
