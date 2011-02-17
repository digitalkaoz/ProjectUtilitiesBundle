<?php

namespace rs\ProjectUtilitiesBundle\Tests\Command;

use rs\ProjectUtilitiesBundle\Command\BootstrapCommand;
use rs\ProjectUtilitiesBundle\Tests\WebTestCase as BaseWebTestCase;
use Symfony\Bundle\FrameworkBundle\Console\Application;
use Symfony\Component\Console\Output\Output;
use Symfony\Component\Console\Tester\CommandTester;
//use Symfony\Bundle\ZendBundle\Logger\Logger;


class BootstrapCommandTest extends BaseWebTestCase
{
	public function testConfiguration()
	{		
		$command = new TestBootstrapCommand();
		$options = $command->getDefinition()->getOptions();
		$this->assertEquals(\array_keys($options),array('config','stop'),'options correct');
		
		$arguments = $command->getDefinition()->getArguments();
		$this->assertEquals(\array_keys($arguments),array(),'arguments correct');
	}
		
    public function testRunTask()
    {
		$this->markTestIncomplete();
		$command = new TestBootstrapCommand();
		$kernel = $this->createKernel();	
		$app = new Application($kernel);
		$command->setApplication($app);
		
        $tester = new CommandTester($command);

        $output = $tester->execute(array(
            '--config' => dirname(__FILE__).'/../Fixtures/bootstrap.yml'
        ),array('interactive'=>false,'decorated'=>false));
		
		var_dump($output);
		
/*		$command = new ActivateUserCommand();
        $application = new Application($kernel);
        $application->setAutoExit(false);
        $tester = new ApplicationTester($application);

        $username = 'test_username';
        $password = 'test_password';
        $email = 'test_email@email.org';

        $userManager = $this->getService('fos_user.user_manager');

        $user = $userManager->createUser();
        $user->setUsername($username);
        $user->setEmail($email);
        $user->setPlainPassword($password);
        $user->setEnabled(false);

        $userManager->updateUser($user);

        $this->assertFalse($user->isEnabled());

        $tester->run(array(
            'command' => $command->getFullName(),
            'username' => $username,
        ), array('interactive' => false, 'decorated' => false, 'verbosity' => Output::VERBOSITY_VERBOSE));

        $this->getService('doctrine.orm.default_entity_manager')->clear();

        $userManager = $this->getService('fos_user.user_manager');
        $user = $userManager->findUserByUsername($username);

        $this->assertTrue($user instanceof User);
        $this->assertTrue($user->isEnabled());

        $userManager->updateUser($user);*/
    }

    protected function tearDown()
    {
    }	
	
}

class TestBootstrapCommand extends BootstrapCommand
{
	public function __call($method, array $args = array()) {
		if (!method_exists($this, $method)){
			throw new BadMethodCallException("method '$method' does not exist");
		}
		return call_user_func_array(array($this,$method), $args);
	}
		
}
