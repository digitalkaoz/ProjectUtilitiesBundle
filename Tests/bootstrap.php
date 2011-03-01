<?php

$_SERVER['SYMFONY2'] = dirname(__FILE__).'/../../../../vendor_full/symfony/src';
require_once $_SERVER['SYMFONY2'].'/Symfony/Component/ClassLoader/UniversalClassLoader.php';
$_SERVER['KERNEL_DIR'] = dirname(__FILE__).'/../../../../app/';

$base_dir = $_SERVER['KERNEL_DIR'].'/../';
use Symfony\Component\ClassLoader\UniversalClassLoader;

$loader = new UniversalClassLoader();
$loader->registerNamespace('Symfony', $_SERVER['SYMFONY2']);
//$loader->registerNamespace('Zend',  $_SERVER['KERNEL_DIR'].'/../vendor_full/zend-log');
//$loader->registerNamespace('Sensio', $base_dir.'src');

$loader->registerNamespaces(array(
//    'Symfony'                        => $_SERVER['SYMFONY2'],
//    'Doctrine\\Common\\DataFixtures' => $base_dir.'/vendor_full/doctrine-data-fixtures/lib',
//    'Doctrine\\Common'               => $base_dir.'/vendor_full/doctrine-common/lib',
//    'Doctrine\\DBAL\\Migrations'     => $base_dir.'/vendor_full/doctrine-migrations/lib',
//    'Doctrine\\MongoDB'              => $base_dir.'/vendor_full/doctrine-mongodb/lib',
//    'Doctrine\\ODM\\MongoDB'         => $base_dir.'/vendor_full/doctrine-mongodb-odm/lib',
//    'Doctrine\\DBAL'                 => $base_dir.'/vendor_full/doctrine-dbal/lib',
//    'Doctrine'                       => $base_dir.'/vendor_full/doctrine/lib',
//    'Zend\\Log'                      => $base_dir.'/vendor_full/zend-log',
    
));
$loader->registerPrefixes(array(
//    'Twig_Extensions_' => $base_dir.'/vendor_full/twig-extensions/lib',
//    'Twig_'            => $base_dir.'/vendor_full/twig/lib',
//    'Swift_'           => $base_dir.'/vendor_full/swiftmailer/lib/classes',
));

$loader->register();

spl_autoload_register(function($class)
{
    if (0 === strpos($class, 'rs\\ProjectUtilitiesBundle\\')) {
        $path = implode('/', array_slice(explode('\\', $class), 2)).'.php';
        if(!file_exists(__DIR__.'/../'.$path)){
            echo __DIR__.'/../'.$path;
            return false;
        }
        require_once __DIR__.'/../'.$path;
        return true;
    }
});
