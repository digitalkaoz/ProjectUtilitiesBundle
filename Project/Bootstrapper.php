<?php
namespace rs\ProjectUtilitiesBundle\Project;

use Symfony\Component\Console\Input\StringInput;
use Symfony\Component\Console\Input\ArrayInput;

class Bootstrapper
{
	protected $application, $output;
	
	public function setApplication($app)
	{
		$this->application = $app;
		
		return $this;
	}
	
	public function setOutput($output)
	{
		$this->output = $output;
		
		return $this;
	}
	
	public function bootstrap($config_file)
	{
		$config = $this->loadConfigFile($config_file);
		
		\array_map(array($this, 'processCommand'), $config['commands']);
	}
	
	/**
	 * loads the config file
	 * 
	 * @param string $file
	 * @return array 
	 */
	protected function loadConfigFile($file = null)
	{
		//default
		if (!$file){
			$file = $this->guessConfigFile();
		}
		
		if (!\is_file($file)) {
			throw new \InvalidArgumentException(sprintf('configuration file not found [%s]', $file));
		}

		return \Symfony\Component\Yaml\Yaml::load($file);
	}
	
	protected function guessConfigFile()
	{
		$kernel = $this->application->getKernel();
		$root = $kernel->getRootDir();
		
		if(\file_exists($root.'/config/bootstrap_'.$kernel->getEnvironment().'.yml'))
		{
			return $root.'/config/bootstrap_'.$kernel->getEnvironment().'.yml';
		}elseif(\file_exists($root.'/config/bootstrap.yml'))
		{
			return $root.'/config/bootstrap.yml';
		}else{
			return dirname(__FILE__).'/../Resources/bootstrap.yml';
		}
	}
	
	/**
	 * runs a command
	 * 
	 * @param string $command
	 * @return int 
	 */
	protected function processCommand($command)
	{
		if($this->output){
			$this->output->writeln(sprintf('<question>execute</question> <comment>%s</comment>', $command));
		}

		//create a input from command line
		$input = strpos($command, ' ') !== false ? new StringInput($command) : new ArrayInput(array($command));

		//run the command
		return $this->application->run($input);
	}	
	
}