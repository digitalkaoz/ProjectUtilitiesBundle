<?php

namespace rs\ProjectUtilitiesBundle\Project;

use Symfony\Component\Yaml\Yaml;

class BundleLoader
{
	/**
	 * loads bundles defined in a file
	 * 
	 * @param string $file
	 * @param string $env
	 * @return bundle 
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
	
	protected static function mergeConfig($config,$env)
	{
		$configAll = isset($config['all']) ? $config['all'] : array();
		$configEnv = isset($config[$env]) ? $config[$env] : array();

		return array_merge($configAll,$configEnv);
	}
	
	
}