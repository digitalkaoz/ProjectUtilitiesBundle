<?php

namespace rs\ProjectUtilitiesBundle\Project;

use Symfony\Component\Yaml\Yaml;
use Symfony\Component\HttpKernel\KernelInterface;

/**
 * a simple yaml to bundle wrapper
 * 
 * @author Robert Schönthal <seroscho@googlemail.com>
 * @package rs.ProjectUtilitiesBundle
 * @subpackage Project
 */
class BundleLoader
{
    protected $kernel;
    
    public static function create(KernelInterface $kernel)
    {
        $instance = new self();
            
        return $instance->setKernel($kernel);
    }
    
    public function setKernel(KernelInterface $kernel)
    {
        $this->kernel = $kernel;
        
        return $this;
    }
    
	/**
	 * loads bundles defined in a file
	 * 
	 * @param string $file
	 * @param string $env
	 * @return array[Bundle] 
	 */
	public function loadFromFile($file)
	{
        $env = $this->kernel->getEnvironment();
        
		if(!\is_readable($file))
		{
			throw new \InvalidArgumentException('file not found '.$file);
		}
        
        $config = $this->read($file, $env);
        
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
	protected function mergeConfig($config,$env)
	{
		$configAll = isset($config['all']) ? $config['all'] : array();
		$configEnv = isset($config[$env]) ? $config[$env] : array();

		return array_merge($configAll,$configEnv);
	}
    
    protected function read($file,$env)
    {
        $cache = $this->kernel->getCacheDir().'/'.basename($file,'.yml').'.cache';
        
        //create cache dir if not exists
        if(!is_dir($this->kernel->getCacheDir()))
        {
            mkdir($this->kernel->getCacheDir(), 0777, true);
        }
        
        //read from cache
        if(is_readable($cache))
        {
            return unserialize(file_get_contents($cache));
        }
        
		$config = $this->mergeConfig(Yaml::parse($file),$env);
        
        file_put_contents($cache, serialize($config));
        
        return $config;
    }
	
	
}