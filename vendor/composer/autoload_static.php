<?php

// autoload_static.php @generated by Composer

namespace Composer\Autoload;

class ComposerStaticInit21b89a43c687d5e990903e6b4c5fdf88
{
    public static $prefixLengthsPsr4 = array (
        'C' => 
        array (
            'Codexpert\\User_Switcher\\App\\' => 28,
            'Codexpert\\User_Switcher\\' => 24,
        ),
    );

    public static $prefixDirsPsr4 = array (
        'Codexpert\\User_Switcher\\App\\' => 
        array (
            0 => __DIR__ . '/../..' . '/app',
        ),
        'Codexpert\\User_Switcher\\' => 
        array (
            0 => __DIR__ . '/../..' . '/classes',
        ),
    );

    public static $classMap = array (
        'Codexpert\\Plugin\\Base' => __DIR__ . '/..' . '/codexpert/plugin/src/Base.php',
        'Codexpert\\Plugin\\Fields' => __DIR__ . '/..' . '/codexpert/plugin/src/Fields.php',
        'Codexpert\\Plugin\\Metabox' => __DIR__ . '/..' . '/codexpert/plugin/src/Metabox.php',
        'Codexpert\\Plugin\\Notice' => __DIR__ . '/..' . '/codexpert/plugin/src/Notice.php',
        'Codexpert\\Plugin\\Settings' => __DIR__ . '/..' . '/codexpert/plugin/src/Settings.php',
        'Codexpert\\Plugin\\Setup' => __DIR__ . '/..' . '/codexpert/plugin/src/Setup.php',
        'Codexpert\\Plugin\\Table' => __DIR__ . '/..' . '/codexpert/plugin/src/Table.php',
        'Codexpert\\Plugin\\Widget' => __DIR__ . '/..' . '/codexpert/plugin/src/Widget.php',
        'Composer\\InstalledVersions' => __DIR__ . '/..' . '/composer/InstalledVersions.php',
        'mukto90\\Ncrypt' => __DIR__ . '/..' . '/mukto90/ncrypt/src/class.ncrypt.php',
    );

    public static function getInitializer(ClassLoader $loader)
    {
        return \Closure::bind(function () use ($loader) {
            $loader->prefixLengthsPsr4 = ComposerStaticInit21b89a43c687d5e990903e6b4c5fdf88::$prefixLengthsPsr4;
            $loader->prefixDirsPsr4 = ComposerStaticInit21b89a43c687d5e990903e6b4c5fdf88::$prefixDirsPsr4;
            $loader->classMap = ComposerStaticInit21b89a43c687d5e990903e6b4c5fdf88::$classMap;

        }, null, ClassLoader::class);
    }
}
