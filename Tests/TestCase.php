<?php

namespace rs\ProjectUtilitiesBundle\Tests;

use Symfony\Bundle\FrameworkBundle\Tests\Kernel;
use Symfony\Bundle\FrameworkBundle\Console\Application;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBag;
use Symfony\Component\DependencyInjection\Loader\YamlFileLoader;

use rs\ProjectUtilitiesBundle\DependencyInjection\BootstrapExtension;

class TestCase extends \Symfony\Bundle\FrameworkBundle\Tests\TestCase
{
    protected $kernel;
	
	public function getKernel()
	{
		if(!$this->kernel)
		{
			$this->kernel = $this->createKernelMock('test');
		}
		
		return $this->kernel;
	}

    public function getApplication()
    {
        $kernel = $this->getKernel();

        return new Application($kernel);
    }

    protected function createKernelMock($name)
    {
        $kernel = $this->getMock('Symfony\Component\HttpKernel\KernelInterface');
        $container = new ContainerBuilder(new ParameterBag(array(
            'kernel.bundles'     => array('YamlBundle' => 'Fixtures\Bundles\YamlBundle\YamlBundle'),
            'kernel.cache_dir'   => sys_get_temp_dir(),
            'kernel.root_dir'    => $_SERVER['KERNEL_DIR'] // src dir
        )));
        $loader = new BootstrapExtension();
        $container->registerExtension($loader);
        $kernel->expects($this->once())->method('getContainer')->will($this->returnValue($container));
        $kernel->expects($this->once())->method('getBundles')->will($this->returnValue(array()));

        return $kernel;
    }
	
	protected function getService($name)
    {
		return $this->getKernel()->getContainer()->get($name);
    }	
}
