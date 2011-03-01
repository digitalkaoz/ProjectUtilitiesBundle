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

use rs\ProjectUtilitiesBundle\Project\Configurator;

/**
 * a simple command to replace various %%TOKENS%% in config files
 * 
 * @author Robert Schönthal <seroscho@googlemail.com>
 * @package rs.ProjectUtitlitiesBundle
 * @subpackage Command
 */
class ConfigureCommand extends Command
{
    protected $configurator;
    
	/**
	 * @see Command
	 */
	protected function configure()
	{
		$this
				->setDefinition(array(
					new InputOption('config', 'c', InputOption::VALUE_OPTIONAL, 'config file to use'),
					new InputOption('list', 'l', InputOption::VALUE_NONE, 'show private config vars'),
                    new InputOption('setup', null, InputOption::VALUE_NONE, 'show the setup'),
                    new InputOption('autoload', null, InputOption::VALUE_NONE, 'autoload'),
				))
				->setHelp(<<<EOT
The <info>project:configure</info> command configures your application with user defined constants

use such tokens in your files to replace them with custom variables:

<comment>
doctrine:
   dbal:
       dbname:   %%DB_NAME%%
       user:     %%DB_USER%%
       password: %%DB_PWD%%
       host:     %%DB_HOST%%
</comment>

<info>./app/console project:configure --config=~/foo.yml</info> loads a custom config file

<info>./app/console project:configure --autoload</info> loads the default config vars without interaction

<info>./app/console project:configure --list</info> shows the current private config vars

<info>./app/console project:configure --setup</info> shows informations about the current setup

a config file looks like: (defaults: app/config/configure.yml)
<comment>
#search for .dist files in these folders
in_dirs:
  - config
  - views

#also replace these files
in_files:   
  - foo.php.dist
  - %%KERNEL.ENVIRONMENT%%/foo.html

#replace tokens with these config vars
variables:
  DB_NAME:
    desc: database name
    default: symfony_%%KERNEL.ENVIRONMENT%%
  DB_PWD:
    desc: database password
    default: symfony
  DB_USER:
    desc: database user
    default: symfony
  DB_HOST:
    desc: database host
    default: localhost
</comment>
EOT
				)
				->setName('project:configure')
                ->setDescription('configures application files with private variables');
		;
	}

	/**
	 * @see Command
	 */
	protected function execute(InputInterface $input, OutputInterface $output)
	{
		$this->output = $output;
        
		if(!$this->application){
			$this->setApplication(new Application($this->container->get('kernel')));
		}

        $this->configurator = Configurator::create($this->application->getKernel());
        $this->configurator->setOutput($output);
        
        //show private vars
        if($input->getOption('list')){
            $this->listVars();
        }
        //list current setup
        elseif($input->getOption('setup')){
            $this->listSetup();
        }
        //configure app
        else{
            $this->runConfiguration($input->getOption('autoload'));
        }
    }
    
    /**
     * configures an app with variables from cli or cache
     * 
     * @param boolean $autoload 
     */
    protected function runConfiguration($autoload = false)
    {
        $setup = $this->configurator->getSetup();
        $defaults = $this->configurator->getConfig();
        //var_dump($defaults);
        $setup = isset($setup['variables']) ? $setup['variables'] : array();
        $new_config = array();
        
        foreach($setup as $k => $config){
            $default = isset($defaults[$k]) ? $defaults[$k] : (isset($config['default']) ? $config['default'] : null) ;
            
            if(!$autoload){
                $input = new ArrayInput(array('--desc'=>isset($config['desc']) ? $config['desc'] : 'a variable','--default'=>$default,'--name'=>$k));
                $result = $this->ask($input, $this->output);
            }
            
            $new_config[$k] = isset($result) && $result !== null ? $result : $default;
        }
        
        $this->configurator->configure($new_config);
    }
	
    /**
     * list private vars
     */
    protected function listVars()
    {
        $vars = $this->configurator->getConfig();
        
        if(!$vars){
            $this->output->writeln(sprintf('<info>no file</info>'));
        }
        
        foreach($vars as $k => $v){
            $this->output->writeln(sprintf('<info>%s</info>  %s',$k,$v));
        }
    }
    
	/**
     * list current setup
     */
    protected function listSetup()
    {
        $vars = $this->configurator->getSetup();
        
        if(!$vars || !is_array($vars)){
            $this->output->writeln(sprintf('<info>no configuration</info>'));
        }
        
        if(isset($vars['in_dirs'])){
            foreach($vars['in_dirs'] as $a=>$b){
                $this->output->writeln(sprintf('<info>in dir</info>  %s',$b));                    
            }            
        }
        $this->output->writeln('');                    

        if(isset($vars['in_files'])){
            foreach($vars['in_files'] as $a=>$b){
                $this->output->writeln(sprintf('<info>in file</info>  %s',$b));                    
            }            
        }
        $this->output->writeln('');                    

        if(isset($vars['variables'])){
            foreach($vars['variables'] as $a=>$b){
                $variable = sprintf(' "%s" : <comment>%s</comment>',isset($b['desc']) ? $b['desc'] : 'a variable',$b['default'] ? $b['default'] : 'default value');
                $this->output->writeln(sprintf('<info>%s</info>%s',$a,$variable));                    
            }            
        }        
    }
    
    /**
     * @see Command::interact
     */
    protected function ask(InputInterface $input, OutputInterface $output)
    {
        return $this->getHelper('dialog')->ask(
            $output,
            sprintf('<info>%s</info> %s <comment>%s</comment>',$input->getParameterOption('--name'),$input->getParameterOption('--desc'),$input->getParameterOption('--default'))
        );
    }
    
    
    
}
