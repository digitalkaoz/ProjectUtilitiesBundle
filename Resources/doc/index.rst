Provides some more RAD symfony2 commands

Features
========

- run commands defined in a yaml file

Installation
============

Add ProjectUtilitiesBundle to your *src/* dir
-------------

::

    $ git submodule add git://github.com/digitalkaoz/ProjectUtilitiesBundle.git src/rs/ProjectUtilitiesBundle
    $ git submodule init


Add the *rs* namespace to your autoloader
-------------

::

    // app/autoload.php

    $loader->registerNamespaces(array(
        'rs' => __DIR__.'/../src',
        // your other namespaces
    );


Add ProjectUtilitiesBundle to your application kernel
-------------


::

    // app/AppKernel.php

    public function registerBundles()
    {
        return array(
            // ...
            new rs\ProjectUtilitiesBundle\ProjectUtilitiesBundle(),
            // ...
        );
    }


Bootstraper
=====================

*the bootstrapper builds an app with console and commands*

configure your commands:
-------------

::

    # app/config/project_bootstrap.yml

    commands:
      - 'doctrine:generate:entities FooBundle'
      - 'doctrine:schema:update'
      - 'help'
  
    shells:
      - 'ls'


run the command
------------

::

    # with the default config
    $ app/console project:bootstrap

    # with a custom config
    $ app/console project:bootstrap --config=~/foo.yml

    # stop if a command fails
    $ app/console project:bootstrap --stop


BundleLoader
=====================

*the BundleLoader manages your bundle config in an yaml file*

use the BundleLoader in your Application Kernel
---------------

::

    // app/AppKernel.php
    use rs\ProjectUtilitiesBundle\Project\BundleLoader;
    
    class AppKernel extends Kernel
    {

    public function registerBundles()
    {
		$file = $this->getRootDir().'/config/bundles.yml';
		return BundleLoader::loadFromConfig($file);
    }


environment configurations
---------------

::

    # app/config/bundles.yml
    all:
      - Symfony\Bundle\FrameworkBundle\FrameworkBundle
      - Symfony\Bundle\TwigBundle\TwigBundle
      - Symfony\Bundle\ZendBundle\ZendBundle
      - Symfony\Bundle\SwiftmailerBundle\SwiftmailerBundle
      - rs\ProjectUtilitiesBundle\ProjectUtilitiesBundle
    
    dev:
      - Symfony\Bundle\DoctrineBundle\DoctrineBundle
      
    test:

