<?php

namespace rs\ProjectUtilitiesBundle\Project;

use Symfony\Component\Console\Input\StringInput;
use Symfony\Component\Console\Input\ArrayInput;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\HttpKernel\KernelInterface;
use Symfony\Component\Yaml\Yaml;
use Symfony\Component\Console\Application;

/**
 * executes various symfony2 or shell commands defined in a yaml file
 * 
 * @author Robert Schönthal <seroscho@googlemail.com>
 * @package rs.ProjectUtilitiesBundle
 * @subpackage Project
 */
class Bootstrapper
{

    protected $kernel, $application, $output, $config_file;

    public function setKernel(KernelInterface $kernel)
    {
        $this->kernel = $kernel;

        return $this;
    }

    public function setConfigFile($file)
    {
        if (!\is_readable($file)) {
            throw new \InvalidArgumentException(sprintf('configuration file not found [%s]', $file));
        }

        $this->config_file = $file;
    }

    public function setOutput(OutputInterface $output)
    {
        $this->output = $output;

        return $this;
    }

    public function setApplication(Application $app)
    {
        $this->application = $app;

        return $this;
    }

    /**
     * bootstraps the application with commands
     * 
     * @param string $config_file 
     */
    public function bootstrap()
    {
        $config = $this->loadConfigFile($this->config_file);

        // run shells
        $this->processShells($config['shells']);

        //run commands
        $this->processCommands($config['commands']);

        return $this;
    }

    /**
     * runs several shell commands
     * 
     * @param array $commands 
     */
    protected function processShells($shells)
    {
        \array_map(array($this, 'processShell'), $shells);
    }

    /**
     * runs a command
     * 
     * @param string $command
     * @return int 
     */
    protected function processShell($command)
    {
        $this->out(sprintf('<question>execute</question> <comment>%s</comment>', $command));

        ob_start();
        system($command);

        $this->out(ob_get_clean());
    }

    /**
     * runs several commands
     * 
     * @param array $commands 
     */
    protected function processCommands($commands)
    {
        if (!$this->application) {
            $this->application = new Application($this->kernel);
        }

        if ($commands) {
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

        return Yaml::parse($file);
    }

    /**
     * reads the config file from the container
     * 
     * @return string 
     */
    protected function getConfigFile()
    {
        return $this->kernel->getContainer()->get('rs_projectutilities.bootstrap.resource');
    }

    /**
     * runs a command
     * 
     * @param string $command
     * @return int 
     */
    protected function processCommand($command)
    {
        $this->out(sprintf('<question>execute</question> <comment>%s</comment>', $command));

        //create a input from command line
        $input = strpos($command, ' ') !== false ? new StringInput($command) : new ArrayInput(array($command));

        //run the command
        //TODO correct input parsing fails on "help --foo bar"
        return $this->application->run($input);
    }
    
    protected function out($message)
    {
        if ($this->output) {
            $this->output->writeln($message);
        }
    }

}