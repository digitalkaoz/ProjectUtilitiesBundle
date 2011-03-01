**Provides some more RAD symfony2 utilities**


Features
========

- **Bootstrapper** *run commands defined in a yaml file to bootstrap an application*
- **BundleLoader** *configure your Bundles in a yaml file*
- **Configurator** *configures your application with private variables ie. database credentials*

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
    
   //or use the BundleLoader (see below)
  

Using DependencyInjection
-------------------------

::

    $this->get('bootstrap');    //returns the bootstrapper
    $this->get('configurator'); //returns the configurator
    $this->get('bundleloader'); //returns the bundleloader


::

    #app/config/config.yml
    project_utilities:
      
      bootstrap:
        class: Bootstrapper
        file: app/config/bootstrap.yml
      
      bundleloader:
        class: Bundleloader
        file: app/config/bundles.yml

      configurator:
        class: Configurator
        setup: app/config/configuration.yml
        dist: .dist #the file extension for placeholder files
        config: /home/YOU/.[KERNEL.NAME]_[KERNEL.ENVIRONMENT].ini #private vars



TODO
----

* more tests
* more sophisticated dic


Bootstrapper
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
            return BundleLoader::loadFromFile($file,$this->getEnvironment());
        }
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


Configurator
===================

*the configurator stores your private variables in a file*
*it replaces placeholders within your files with those variables*

define the configuration
------------------------

::

    # app/config/configuration.yml
    in_dirs:
      - config
      - views
    
    in_files:
      - bootstrap_%%KERNEL.ENViRONMENT%%.php
      
    variables:
      DB_NAME:
        desc: database name
        default: symfony_%%KERNEL.ENVIRONMENT%%
      DB_PWD:
        desc: database password
        default: symfony
      DB_USER:
        desc: database user
        default: symfony
      DB_HOST:
        desc: database host
        default: localhost

use the following placeholder format (file format doesnt matter):

::

    #app/config/config.yml.dist
    doctrine:
     dbal:
       dbname:   %%DB_NAME%%
       user:     %%DB_USER%%
       password: %%DB_PWD%%


all files with extension **.dist** will be parsed and replaced with tokens!

these **.dist** files can be stored in your vcs 

**dont check in password or private configurations**

(when the configurator runs it creates placeholder replaced copies without the **.dist** extension)

run the command
---------------

::

    # with the default config (/home/YOU/.[KERNEL.NAME]_[KERNEL.ENVIRONMENT].ini)
    $ app/console project:configure

    # list current config variables
    $ app/console project:configure --list

    # lists current setup
    $ app/console project:configure --setup