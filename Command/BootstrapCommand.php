<?php

namespace rs\ProjectUtilitiesBundle\Command;

use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Output\Output;
use Symfony\Component\HttpKernel\KernelInterface;
use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;

use rs\ProjectUtilitiesBundle\Project\Bootstrapper;

/**
 * a simple command to bootstrap the application
 * 
 * @author Robert Schönthal <seroscho@googlemail.com>
 * @package rs.ProjectUtitlitiesBundle
 * @subpackage Command
 */
class BootstrapCommand extends ContainerAwareCommand
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
                ->setDescription('bootstraps an application with various commands')
		;
	}

	/**
	 * @see Command
	 */
	protected function execute(InputInterface $input, OutputInterface $output)
	{
		$this->getApplication()->setCatchExceptions(!$input->getOption('stop'));
		$this->getApplication()->setAutoExit(false);
		
		$bootstrapper = $this->getContainer()->get('rs_projectutilities.bootstrap');
		
		$bootstrapper->
            setApplication($this->getApplication())->
			setOutput($output)->
			bootstrap();
	}
	
}
