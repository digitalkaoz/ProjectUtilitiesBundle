<?php

require_once $_SERVER['SYMFONY2'].'/Symfony/Component/ClassLoader/UniversalClassLoader.php';
$_SERVER['KERNEL_DIR'] = dirname(__FILE__).'/../../../../app/';

$base_dir = $_SERVER['KERNEL_DIR'].'/../';
use Symfony\Component\ClassLoader\UniversalClassLoader;

$loader = new UniversalClassLoader();
$loader->registerNamespace('Symfony', $_SERVER['SYMFONY2']);
$loader->registerNamespace('Zend',  $_SERVER['KERNEL_DIR'].'/../vendor/zend/library');
$loader->registerNamespace('Sensio', $base_dir.'src');

$loader->registerPrefixes(array(
    'Twig_Extensions_' => $base_dir.'/vendor/twig-extensions/lib',
    'Twig_'            => $base_dir.'/vendor/twig/lib',
    'Swift_'           => $base_dir.'/vendor/swiftmailer/lib/classes',
));

$loader->register();

spl_autoload_register(function($class)
{
    if (0 === strpos($class, 'rs\\ProjectUtilitiesBundle\\')) {
        $path = implode('/', array_slice(explode('\\', $class), 2)).'.php';
        require_once __DIR__.'/../'.$path;
        return true;
    }
});
