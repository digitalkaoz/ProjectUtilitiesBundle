<?php

namespace rs\ProjectUtilitiesBundle\Command;

use Symfony\Bundle\FrameworkBundle\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Output\Output;
use Symfony\Bundle\FrameworkBundle\Util\Mustache;

use Symfony\Bundle\FrameworkBundle\Console\Application;

/**
 * Bootstraps a project
 *
 * @author Robert Schönthal <seroscho@googlemail.com>
 */
class BootstrapCommand extends Command
{
	private $defaultConfig = 'project_bootstrap.yml';
	
    /**
     * @see Command
     */
    protected function configure()
    {		
        $this
            ->setDefinition(array(
				new InputOption('config', 'c', InputOption::VALUE_OPTIONAL, 'config file to use',$this->defaultConfig),
				new InputOption('stop', null, InputOption::VALUE_NONE, 'stop on error')
            ))
            ->setHelp(<<<EOT
The <info>project:bootstrap</info> command bootstraps your application with symfony commands.

<info>./app/console project:bootstrap --config=~/foo.yml</info> loads a custom config

a config file looks like: (defaults: app/config/project_bootstrap.yml)<comment>
commands:
  - 'doctrine:generate:entities FooBundle'
  - 'doctrine:schema:update'
  - 'router:debug --foo bar'
  
shells:
  - 'ls'
</comment>
<info>./app/console project:bootstrap --stop</info> may raise an exception
EOT
            )
            ->setName('project:bootstrap')
        ;
    }

    /**
     * @see Command
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
		$this->output = $output;
		$this->stopOnError = $input->getOption('stop');
		$config = $this->loadConfigFile($input->getOption('config'));
		
		$this->setApplication(new Application($this->container->get('kernel')));
		$this->application->setCatchExceptions(!$this->stopOnError);
		$this->application->setAutoExit(false);

		$this->doCommands($config['commands']);
    }
	
	protected function loadConfigFile($file)
	{
		//default
		if($file === $this->defaultConfig)
		{
			$kernel = $this->container->get('kernel');
			$file = $kernel->getRootDir().\DIRECTORY_SEPARATOR.'config'.\DIRECTORY_SEPARATOR.$file;
		}
		
		if(!\is_file($file))
		{
			throw new \InvalidArgumentException(sprintf('configuration file not found [%s]',$file));
		}
		
		return \Symfony\Component\Yaml\Yaml::load($file);
	}
	
	protected function doCommands($commands)
	{
		//supress warnings if stop on error
		if($this->stopOnError){
			@\array_map(array($this,'processCommand'), $commands);
		}else{
			\array_map(array($this,'processCommand'), $commands);
		}
		
	}
	
	protected function processCommand($command)
	{
		$this->output->writeln(sprintf('<question>execute</question> <comment>%s</comment>', $command));

		//create a input from command line
		$input = strpos($command,' ') !== false ? new \Symfony\Component\Console\Input\StringInput($command) : new \Symfony\Component\Console\Input\ArrayInput(array($command));
	
		//run the command
		return $this->application->run($input);
	}
	
	
}
