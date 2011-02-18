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
use Symfony\Component\DependencyInjection\Loader\XmlFileLoader;
use Symfony\Component\DependencyInjection\ContainerBuilder;

/**
 */
class ProjectUtilitiesExtension extends Extension
{
    public function configLoad(array $configs, ContainerBuilder $container)
    {		
		$this->bootstrapLoad($config,$container);
    }
	
    public function bootstrapLoad(array $configs, ContainerBuilder $container)
    {		
        $loader = new XmlFileLoader($container, __DIR__.'/../Resources/config');
        $loader->load('bootstrap.xml');
		
        $this->addClassesToCompile(array(
            'Bootstrapper'
        ));
		
        foreach ($configs as $config) {
            $this->doConfigLoad($config, $container);
        }
    }
	
    /**
     *
     * @param array            $config    An array of configuration settings
     * @param ContainerBuilder $container A ContainerBuilder instance
     */
    protected function doConfigLoad(array $config, ContainerBuilder $container)
    {		
		if(!$container->hasDefinition('bootstrap.file'))
		{
            $container->setParameter('bootstrap.file', $config['file']);
        }	
		
		$container->setAlias('bootstrap','bootstrap');
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
        return 'projectutilities';
    }
}
