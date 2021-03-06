<?php

// autoload_static.php @generated by Composer

namespace Composer\Autoload;

class ComposerStaticInit88cd77bd4c7d5540a33a015f2ea1fbdd
{
    public static $files = array (
        '57eda60a53ac3dd61f3920b2d0072b8e' => __DIR__ . '/../..' . '/includes/functions.php',
    );

    public static $prefixLengthsPsr4 = array (
        'W' => 
        array (
            'WeDevs\\Tutorial\\' => 16,
        ),
    );

    public static $prefixDirsPsr4 = array (
        'WeDevs\\Tutorial\\' => 
        array (
            0 => __DIR__ . '/../..' . '/includes',
        ),
    );

    public static $classMap = array (
        'Composer\\InstalledVersions' => __DIR__ . '/..' . '/composer/InstalledVersions.php',
    );

    public static function getInitializer(ClassLoader $loader)
    {
        return \Closure::bind(function () use ($loader) {
            $loader->prefixLengthsPsr4 = ComposerStaticInit88cd77bd4c7d5540a33a015f2ea1fbdd::$prefixLengthsPsr4;
            $loader->prefixDirsPsr4 = ComposerStaticInit88cd77bd4c7d5540a33a015f2ea1fbdd::$prefixDirsPsr4;
            $loader->classMap = ComposerStaticInit88cd77bd4c7d5540a33a015f2ea1fbdd::$classMap;

        }, null, ClassLoader::class);
    }
}
