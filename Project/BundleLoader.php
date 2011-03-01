<?php

namespace rs\ProjectUtilitiesBundle\Project;

use Symfony\Component\Yaml\Yaml;
use Symfony\Component\HttpKernel\Kernel;

use rs\ProjectUtilitiesBundle\DependencyInjection\ProjectUtilitiesExtension;

/**
 * a simple yaml to bundle wrapper
 * 
 * @author Robert Schönthal <seroscho@googlemail.com>
 * @package rs.ProjectUtitlitiesBundle
 * @subpackage Project
 */
class BundleLoader
{
    /**
     * loads the bundles with dic
     * 
     * @param Kernel $kernel 
     */
    public static function load(\Symfony\Component\HttpKernel\KernelInterface $kernel)
    {
        //var_dump($kernel);
        //$loader = new ProjectUtilitiesExtension();        
        //var_dump($loader->load($kernel->get, $container));
    }
    
	/**
	 * loads bundles defined in a file
	 * 
	 * @param string $file
	 * @param string $env
	 * @return array[Bundle] 
	 */
	public static function loadFromFile($file, $env='all')
	{
		if(!\is_file($file))
		{
			throw new \InvalidArgumentException('file not found '.$file);
		}
		
		$config = self::mergeConfig(Yaml::load($file),$env);
		
		$bundles = array();
		
		foreach($config as $bundle)
		{
			$bundles[] = new $bundle();
		}
		
		return $bundles;
	}
	
    /**
     * flattens config
     * 
     * @param array $config
     * @param string $env
     * @return array 
     */
	protected static function mergeConfig($config,$env)
	{
		$configAll = isset($config['all']) ? $config['all'] : array();
		$configEnv = isset($config[$env]) ? $config[$env] : array();

		return array_merge($configAll,$configEnv);
	}
	
	
}