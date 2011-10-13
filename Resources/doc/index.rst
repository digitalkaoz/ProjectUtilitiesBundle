**Provides some more RAD symfony2 utilities**


Features
========

- **Bootstrapper** *run commands defined in a yaml file to bootstrap an application*
- **BundleLoader** *configure your Bundles in a yaml file*

Installation
============

Add ProjectUtilitiesBundle to your *vendors/* dir
-------------

## via submodules

    $ git submodule add git://github.com/digitalkaoz/ProjectUtilitiesBundle.git src/rs/ProjectUtilitiesBundle
    $ git submodule init

##via ``deps``file

    [rsProjectUtilitiesBundle]
       git=git://github.com/digitalkaoz/ProjectUtilitiesBundle.git
       target=bundles/rs/ProjectUtilitiesBundle



Add the *rs* namespace to your autoloader
-------------

``` php
<?php
    // app/autoload.php

    $loader->registerNamespaces(array(
        'rs' => __DIR__.'/../src',
        // your other namespaces
    );
```

Add ProjectUtilitiesBundle to your application kernel
-------------

``` php
    // app/AppKernel.php
    public function registerBundles()
    {
        return array(
            // ...
            new rs\ProjectUtilitiesBundle\ProjectUtilitiesBundle(),
            // ...
        );
    }
    
   //or use the BundleLoader (see below)
```  

Configuration
-------------

    #app/config/config.yml
    rs_projectutilities:      
      bootstrap:
        class: rs\ProjectUtilitiesBundle\Project\Bootstrapper
        file: %kernel.root_dir%/config/bootstrap.yml
      
Bootstrapper
=====================

*the bootstrapper builds an app with console and commands*

configure your commands:
-------------

    # app/config/project_bootstrap.yml

    commands:
      - 'doctrine:generate:entities FooBundle'
      - 'doctrine:schema:update'
      - 'help'
  
    shells:
      - 'ls'


run the command
------------

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

``` php
    // app/AppKernel.php
    use rs\ProjectUtilitiesBundle\Project\BundleLoader;
    
    class AppKernel extends Kernel
    {
     
        public function registerBundles()
        {
            $file = $this->getRootDir().'/config/bundles.yml';
            return BundleLoader::create($this)->loadFromFile($file);
        }
    }
```

environment configurations
---------------

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