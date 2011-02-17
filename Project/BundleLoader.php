<?php

namespace rs\ProjectUtilitiesBundle\Project;

use Symfony\Component\Yaml\Yaml;

class BundleLoader
{
	protected static $default = array('all');
	
	public static function loadFromFile($file)
	{
		if(!\is_file($file))
		{
			throw new \InvalidArgumentException('file not found '.$file);
		}
		
		$config = \array_merge(self::$default,Yaml::load($file));
		
		$bundles = array();
		
		//$this->getEnvironment();
		foreach($config['all'] as $bundle)
		{
			$bundles[] = new $bundle();
		}
		
		return $bundles;
	}
	
	
}