<?php

namespace rs\ProjectUtilitiesBundle\Project;

use Symfony\Component\Console\Input\StringInput;
use Symfony\Component\Console\Input\ArrayInput;
use Symfony\Component\Console\Output\ConsoleOutput;
use Symfony\Bundle\FrameworkBundle\Console\Application;

/**
 * executes various symfony2 commands defined in a yaml file
 * 
 * @author Robert Schönthal <seroscho@googlemail.com>
 * @package rs.ProjectUtitlitiesBundle
 * @subpackage Project
 */
class Bootstrapper
{

    protected $application, $output;

    public function setApplication($app = null)
    {
        $this->application = $app;

        return $this;
    }

    public function setOutput($output)
    {
        $this->output = $output;

        return $this;
    }

    public function createApplication($kernel)
    {
        $this->setApplication(new Application($kernel));

        return $this;
    }

    /**
     * bootstraps the application with commands
     * 
     * @param string $config_file 
     */
    public function bootstrap($config_file = null)
    {
        if(!$this->application){
            throw new \RuntimeException('set application first');
        }
        
        $config = $this->loadConfigFile($config_file);

        //run commands
        $this->processCommands($config['commands']);
        
        //TODO run shells
        
        return $this;
    }
    
    /**
     * runs several commands
     * 
     * @param array $commands 
     */
    protected function processCommands($commands)
    {
        if($commands){
            \array_map(array($this, 'processCommand'), $commands);            
        }        
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
        if (!$file) {
            $file = $this->getConfigFile();
        }

        if (!\is_file($file)) {
            throw new \InvalidArgumentException(sprintf('configuration file not found [%s]', $file));
        }

        return \Symfony\Component\Yaml\Yaml::load($file);
    }

    /**
     * reads the config file from the container
     * 
     * @return string 
     */
    protected function getConfigFile()
    {
        $kernel = $this->application->getKernel();
        
        return $kernel->getContainer()->parameters['bootstrap.file'];
    }

    /**
     * runs a command
     * 
     * @param string $command
     * @return int 
     */
    protected function processCommand($command)
    {
        if ($this->output) {
            $this->output->writeln(sprintf('<question>execute</question> <comment>%s</comment>', $command));
        }

        //create a input from command line
        $input = strpos($command, ' ') !== false ? new StringInput($command) : new ArrayInput(array($command));
        //run the command
        return $this->application->run($input);
    }

}