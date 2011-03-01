<?php

/*
 * This file is part of the Symfony package.
 *
 * (c) Fabien Potencier <fabien.potencier@symfony-project.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace rs\ProjectUtilitiesBundle\Tests;

use Symfony\Component\HttpKernel\Kernel as BaseKernel;
use Symfony\Bundle\FrameworkBundle\Util\Filesystem;
use Symfony\Component\ClassLoader\UniversalClassLoader;
use Symfony\Component\Config\Loader\LoaderInterface;

class Kernel extends BaseKernel
{
    public function __construct()
    {
        $this->tmpDir = sys_get_temp_dir().'/sf2_'.rand(1, 9999);
        if (!is_dir($this->tmpDir)) {
            if (false === @mkdir($this->tmpDir)) {
                die(sprintf('Unable to create a temporary directory (%s)', $this->tmpDir));
            }
        } elseif (!is_writable($this->tmpDir)) {
            die(sprintf('Unable to write in a temporary directory (%s)', $this->tmpDir));
        }

        parent::__construct('test', true);

        $loader = new UniversalClassLoader();
        $loader->registerNamespaces(array(
            'rs'      => __DIR__.'/../',
        ));
        $loader->register();
    }
    
    public function getName()
    {
        return 'project_utilities';
    }
    
    public function __destruct()
    {
        $fs = new Filesystem();
        $fs->remove($this->tmpDir);
    }

    public function registerRootDir()
    {
        return $this->tmpDir;
    }

    public function registerBundles()
    {
        return array(
            new \Symfony\Bundle\FrameworkBundle\FrameworkBundle(),
            new \rs\ProjectUtilitiesBundle\ProjectUtilitiesBundle()
        );
    }

    public function registerContainerConfiguration(LoaderInterface $loader)
    {
        $loader->load(function ($container) {
            $container->setParameter('kernel.compiled_classes', array());
        });
    }
}
