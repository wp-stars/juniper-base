<?php

// autoload_static.php @generated by Composer

namespace Composer\Autoload;

class ComposerStaticInitacb056cba8b66d7e72f8c1b64603b46b
{
    public static $files = array (
        '320cde22f66dd4f5d3fd621d3e88b98f' => __DIR__ . '/..' . '/symfony/polyfill-ctype/bootstrap.php',
        '0e6d7bf4a5811bfa5cf40c5ccd6fae6a' => __DIR__ . '/..' . '/symfony/polyfill-mbstring/bootstrap.php',
        '25072dd6e2470089de65ae7bf11d3109' => __DIR__ . '/..' . '/symfony/polyfill-php72/bootstrap.php',
        '32a4c26a4fcb93d787a6a7cafb8c7c9d' => __DIR__ . '/..' . '/palmiak/timber-acf-wp-blocks/timber-acf-wp-blocks.php',
    );

    public static $prefixLengthsPsr4 = array (
        'T' => 
        array (
            'Twig\\' => 5,
            'Timber\\' => 7,
        ),
        'S' => 
        array (
            'Symfony\\Polyfill\\Php72\\' => 23,
            'Symfony\\Polyfill\\Mbstring\\' => 26,
            'Symfony\\Polyfill\\Ctype\\' => 23,
            'ScssPhp\\ScssPhp\\' => 16,
        ),
        'J' => 
        array (
            'Juniper\\' => 8,
        ),
        'C' => 
        array (
            'Composer\\Installers\\' => 20,
        ),
    );

    public static $prefixDirsPsr4 = array (
        'Twig\\' => 
        array (
            0 => __DIR__ . '/..' . '/twig/twig/src',
        ),
        'Timber\\' => 
        array (
            0 => __DIR__ . '/..' . '/timber/timber/lib',
        ),
        'Symfony\\Polyfill\\Php72\\' => 
        array (
            0 => __DIR__ . '/..' . '/symfony/polyfill-php72',
        ),
        'Symfony\\Polyfill\\Mbstring\\' => 
        array (
            0 => __DIR__ . '/..' . '/symfony/polyfill-mbstring',
        ),
        'Symfony\\Polyfill\\Ctype\\' => 
        array (
            0 => __DIR__ . '/..' . '/symfony/polyfill-ctype',
        ),
        'ScssPhp\\ScssPhp\\' => 
        array (
            0 => __DIR__ . '/..' . '/scssphp/scssphp/src',
        ),
        'Juniper\\' => 
        array (
            0 => __DIR__ . '/../..' . '/inc',
        ),
        'Composer\\Installers\\' => 
        array (
            0 => __DIR__ . '/..' . '/composer/installers/src/Composer/Installers',
        ),
    );

    public static $fallbackDirsPsr4 = array (
        0 => __DIR__ . '/..' . '/twig/cache-extension/lib',
    );

    public static $prefixesPsr0 = array (
        'T' => 
        array (
            'Twig_' => 
            array (
                0 => __DIR__ . '/..' . '/twig/twig/lib',
            ),
        ),
        'R' => 
        array (
            'Routes' => 
            array (
                0 => __DIR__ . '/..' . '/upstatement/routes',
            ),
        ),
    );

    public static $classMap = array (
        'AltoRouter' => __DIR__ . '/..' . '/altorouter/altorouter/AltoRouter.php',
        'Composer\\InstalledVersions' => __DIR__ . '/..' . '/composer/InstalledVersions.php',
    );

    public static function getInitializer(ClassLoader $loader)
    {
        return \Closure::bind(function () use ($loader) {
            $loader->prefixLengthsPsr4 = ComposerStaticInitacb056cba8b66d7e72f8c1b64603b46b::$prefixLengthsPsr4;
            $loader->prefixDirsPsr4 = ComposerStaticInitacb056cba8b66d7e72f8c1b64603b46b::$prefixDirsPsr4;
            $loader->fallbackDirsPsr4 = ComposerStaticInitacb056cba8b66d7e72f8c1b64603b46b::$fallbackDirsPsr4;
            $loader->prefixesPsr0 = ComposerStaticInitacb056cba8b66d7e72f8c1b64603b46b::$prefixesPsr0;
            $loader->classMap = ComposerStaticInitacb056cba8b66d7e72f8c1b64603b46b::$classMap;

        }, null, ClassLoader::class);
    }
}
