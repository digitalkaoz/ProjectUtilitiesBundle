<?php

namespace rs\ProjectUtilitiesBundle\Tests\DependencyInjection;

use Symfony\Bundle\FrameworkBundle\Console\Application;
use Symfony\Component\Console\Output\Output;
use Symfony\Component\Console\Tester\ApplicationTester;
use Symfony\Component\Console\Output\NullOutput;

use rs\ProjectUtilitiesBundle\Tests\TestCase as BaseTestCase;


class ProjectUtilitiesExtensionTest extends BaseTestCase
{
	
	
    public function testGetService()
    {
		$this->markTestIncomplete();
		$service = $this->getKernel()->getContainer()->get('bootstrap');
		var_dump($service);
	}	
		
    protected function tearDown()
    {
    }	
	
}