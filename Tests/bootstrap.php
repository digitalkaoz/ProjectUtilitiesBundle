<?php

require_once $_SERVER['SYMFONY2'].'/Symfony/Component/ClassLoader/UniversalClassLoader.php';
$_SERVER['KERNEL_DIR'] = dirname(__FILE__).'/../../../../app/';

use Symfony\Component\ClassLoader\UniversalClassLoader;

$loader = new UniversalClassLoader();
$loader->registerNamespace('Symfony', $_SERVER['SYMFONY2']);
$loader->registerNamespace('Zend',  $_SERVER['KERNEL_DIR'].'/../vendor/zend/library');
$loader->register();

spl_autoload_register(function($class)
{
    if (0 === strpos($class, 'rs\\ProjectUtilitiesBundle\\')) {
        $path = implode('/', array_slice(explode('\\', $class), 2)).'.php';
        require_once __DIR__.'/../'.$path;
        return true;
    }
});
