<?php

namespace rs\ProjectUtilitiesBundle\Tests\Project;

use rs\ProjectUtilitiesBundle\Project\BundleLoader;
use rs\ProjectUtilitiesBundle\Tests\TestCase as BaseTestCase;


class BundleLoaderTest extends BaseTestCase
{
	
	/**
	 * @expectedException InvalidArgumentException
	 */
	public function testInvalidLoadConfigFile()
	{		
		$l = BundleLoader::loadFromFile('/foo.yml');
	}

	public function testLoadConfigFile()
	{
		$cfg = BundleLoader::loadFromFile(dirname(__FILE__).'/../../Resources/bundles.yml');
		
		$this->assertTrue(is_array($cfg),true,'config file loaded');
	}
	
}
