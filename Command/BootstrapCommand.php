<?php

namespace rs\ProjectUtilitiesBundle\Command;

use Symfony\Bundle\FrameworkBundle\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Input\StringInput;
use Symfony\Component\Console\Input\ArrayInput;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Output\Output;
use Symfony\Bundle\FrameworkBundle\Util\Mustache;
use Symfony\Bundle\FrameworkBundle\Console\Application;

use rs\ProjectUtilitiesBundle\Project\Bootstrapper;

/**
 * a simple command to bootstrap the application
 * 
 * @author Robert Schönthal <seroscho@googlemail.com>
 * @package rs.ProjectUtitlitiesBundle
 * @subpackage Command
 */
class BootstrapCommand extends Command
{
	/**
	 * @see Command
	 */
	protected function configure()
	{
		$this
				->setDefinition(array(
					new InputOption('config', 'c', InputOption::VALUE_OPTIONAL, 'config file to use'),
					new InputOption('stop', null, InputOption::VALUE_NONE, 'stop on error')
				))
				->setHelp(<<<EOT
The <info>project:bootstrap</info> command bootstraps your application with symfony commands.

<info>./app/console project:bootstrap --config=~/foo.yml</info> loads a custom config

a config file looks like: (defaults: app/config/bootstrap_ENV.yml)<comment>
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

		if(!$this->application){
			$this->setApplication(new Application($this->container->get('kernel')));
		}
		
		$this->application->setCatchExceptions(!$this->stopOnError);
		$this->application->setAutoExit(false);
		
		$bootstrapper = $this->application->getKernel()->getContainer()->get('bootstrap');
		
		//$bootstrapper = new Bootstrapper();

		$bootstrapper->
			setApplication($this->application)->
			setOutput($output)->
			bootstrap($input->getOption('config'));
	}
	
}
