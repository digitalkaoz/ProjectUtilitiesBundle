<?php

/*
 * This file is part of the Symfony package.
 *
 * (c) Fabien Potencier <fabien.potencier@symfony-project.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace rs\ProjectUtilitiesBundle\DependencyInjection;

use Symfony\Component\HttpKernel\DependencyInjection\Extension;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Loader\XmlFileLoader;
use Symfony\Component\Config\FileLocator;
use Symfony\Component\Config\Definition\Processor;
/**
 */
class ProjectUtilitiesExtension extends Extension
{
    public function load(array $configs, ContainerBuilder $container)
    {
        $loader = new XmlFileLoader($container, new FileLocator(__DIR__ . '/../Resources/config'));
        $loader->load('bootstrap.xml');
        
        //if(isset($configs))
        //var_dump($configs);
        
        $configuration = new Configuration();
        $processor = new Processor();
        $config = $processor->process($configuration->getConfigTree($container->getParameter('kernel.debug')), $configs);
        
        if (isset($config['bootstrap'])) {
            $this->loadBootstrapConfig($container,$config['bootstrap']);
        }
    }
    
    protected function loadBootstrapConfig($container, $config)
    {
        if (isset($config['class'])) {
            $container->setParameter('bootstrap.class', $config['class']);
        }
        if (isset($config['file'])) {
            $container->setParameter('bootstrap.file', $config['file']);
        }
    }
	
	
    /**
     * Returns the base path for the XSD files.
     *
     * @return string The XSD base path
     */
    public function getXsdValidationBasePath()
    {
        return __DIR__.'/../Resources/config/schema';
    }

    public function getNamespace()
    {
        return 'http://www.symfony-project.org/schema/dic/bootstrap';
    }

    public function getAlias()
    {
        return 'project_utilities';
    }
}
