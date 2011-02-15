Provides some more RAD symfony2 commands

Features
========

- run commands defined in a yaml file

Installation
============

Add ProjectUtilitiesBundle to your src/ dir
-------------------------------------

::

    $ git submodule add git://github.com/digitalkaoz/ProjectUtilitiesBundle.git src/rs/ProjectUtilitiesBundle


Add the rs namespace to your autoloader
----------------------------------------

::
    // app/autoload.php

    $loader->registerNamespaces(array(
        'rs' => __DIR__.'/../src',
        // your other namespaces
    );


Add ProjectUtilitiesBundle to your application kernel
-----------------------------------------

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


Application Bootstraper
=====================


configure your commands in [app/config/project_bootstrap.yml]:
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

    app/console project:bootstrap

    # with a custom config

    app/console project:bootstrap --config=~/foo.yml

    # stop if a command fails

    app/console project:bootstrap --stop

