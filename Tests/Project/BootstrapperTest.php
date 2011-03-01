<?php

namespace rs\ProjectUtilitiesBundle\Tests\Project;

use rs\ProjectUtilitiesBundle\Project\Bootstrapper;
use rs\ProjectUtilitiesBundle\Tests\TestCase as BaseTestCase;
use Symfony\Bundle\FrameworkBundle\Console\Application;
#use Symfony\Component\Console\Output\Output;
#use Symfony\Component\Console\Tester\ApplicationTester;
use Symfony\Component\Console\Output\NullOutput;


class BootstrapperTest extends BaseTestCase
{
	
	/**
	 * @expectedException InvalidArgumentException
	 * @dataProvider provider
	 */
	public function testInvalidLoadConfigFile(TestBootstrapper $b)
	{		
		$file = '/foo.yml';
		
		$this->assertTrue($b->loadConfigFile($file),array(),'config file loaded');
	}

	/**
	 * @dataProvider provider
	 */
	public function testDefaultLoadConfigFile(TestBootstrapper $b)
	{	
		$cfg = $b->loadConfigFile();
		
		$this->assertTrue(is_array($cfg),true,'config file loaded');
		$this->assertTrue(\array_key_exists('commands',$cfg),true,'commands set');
		$this->assertTrue(\array_key_exists('shells',$cfg),true,'shells set');
	}
	
	/**
	 * @dataProvider provider
	 */
	public function testLoadConfigFile(TestBootstrapper $b)
	{
		$file = dirname(__FILE__).'/../Fixtures/app/config/bootstrap.yml';
		
		$cfg = $b->loadConfigFile($file);
		
		$this->assertTrue(is_array($cfg),true,'config file loaded');
		$this->assertTrue(\array_key_exists('commands',$cfg) && count($cfg['commands']),true,'commands set');
		$this->assertTrue(\array_key_exists('shells',$cfg) && count($cfg['shells']),true,'shells set');
	}
	
	/**
	 * @dataProvider provider
	 */
    public function testProcessCommand(TestBootstrapper $b)
    {
		$b->setOutput(new NullOutput());
		$mock = $this->getMock('Application',array('run'));
		$b->setApplication($mock);		
		
		$b->processCommand('foo');
	}	
	
	/**
	 * @dataProvider provider
	 */
    public function testBootstrap(TestBootstrapper $b)
    {
		$file = dirname(__FILE__).'/../Fixtures/app/config/bootstrap.yml';
		$b->setOutput(new NullOutput());
		$mock = $this->getMock('Application',array('run'));
		$b->setApplication($mock);		
		
		$b->bootstrap($file);
	}	

    protected function tearDown()
    {
    }	
	
	public function provider()
	{
        $kernel = $this->getKernel();
        
        $fs = new \Symfony\Bundle\FrameworkBundle\Util\Filesystem();
        $fs->mirror(__DIR__.'/../Fixtures/app/', $kernel->getRootDir());
        $b = new TestBootstrapper();
        $b->setApplication(new Application($kernel));
                
		return array(
			array($b)
		);
	}
	
}

class TestBootstrapper extends Bootstrapper
{
	public function __call($method, array $args = array()) {
		if (!method_exists($this, $method)){
			throw new BadMethodCallException("method '$method' does not exist");
		}
		return call_user_func_array(array($this,$method), $args);
	}
		
}
