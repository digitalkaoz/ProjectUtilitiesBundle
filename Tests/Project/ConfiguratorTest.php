<?php

namespace rs\ProjectUtilitiesBundle\Tests\Project;

use rs\ProjectUtilitiesBundle\Project\Configurator;
use rs\ProjectUtilitiesBundle\Tests\TestCase as BaseTestCase;
use Symfony\Component\Yaml\Yaml;
use Symfony\Component\Console\Output\NullOutput;

class ConfiguratorTest extends BaseTestCase
{

    public function testCreate()
    {
        $instance = Configurator::create($this->getKernel());

        $this->assertInstanceOf('rs\ProjectUtilitiesBundle\Project\Configurator', $instance, ' create returns an instance');

        $file = \sys_get_temp_dir() . '/' . time();
        touch($file);
        $instance = Configurator::create($this->getKernel(), $file);

        $this->assertInstanceOf('rs\ProjectUtilitiesBundle\Project\Configurator', $instance, ' create returns an instance');

        unlink($file);
    }

    /**
     * @expectedException InvalidArgumentException
     */
    public function testLoadConfigFail()
    {
        $file = 'foo.zip';
        Configurator::create($this->getKernel())->loadConfig($file);
    }

    /**
     * @dataProvider provider
     */
    public function testLoadConfigFile(Configurator $c)
    {
        $file = sys_get_temp_dir() . '/' . time();
        touch($file);
        file_put_contents($file, <<<EOF
[myapp]
ACONFIG=foo
BCONFIG=bar
EOF
        );

        $this->assertSame($c, $c->loadConfig($file));
        $this->assertSame(array('ACONFIG' => 'foo', 'BCONFIG' => 'bar'), $c->getConfig());

        \unlink($file);
    }

    /**
     * @dataProvider provider
     */
    public function testGetDefaultConfig(Configurator $c)
    {
        $file = getenv('HOME').'/.project_utilities_test.ini';
        touch($file);
        
        $method = new \ReflectionMethod(get_class($c), 'getDefaultConfigFile');
        $method->setAccessible(true);
        
        $this->assertSame($file, $method->invoke($c));
        
        unlink($file);
    }

    /**
     * @dataProvider provider
     */
    public function testSetFilesystem(Configurator $c)
    {
        $res = $c->setFilesystem(new \Symfony\Bundle\FrameworkBundle\Util\Filesystem());
        
        $this->assertSame($res,$c);
    }
    
    /**
     * @dataProvider provider
     */
    public function testSetOutput(Configurator $c)
    {
        $res = $c->setOutput(new NullOutput());
        
        $this->assertSame($res,$c);
    }
    
    /**
     * @dataProvider provider
     */
    public function testWriteConfig(Configurator $c)
    {
        $default = getenv('HOME') . DIRECTORY_SEPARATOR . '.' . $this->getKernel()->getName() . '_test.ini';
        $this->assertSame($c, $c->writeConfig());

        $this->assertTrue(\file_exists($default), true, 'default config written');
        \unlink($default);
        $this->assertSame($c, $c->writeConfig($default));

        $this->assertTrue(\file_exists($default), true, 'default config written');
        \unlink($default);
    }

    /**
     * @dataProvider provider
     */
    public function testConfigure(Configurator $c)
    {
        $default = getenv('HOME') . DIRECTORY_SEPARATOR . '.' . $this->getKernel()->getName() . '_test.ini';

        $configs = array('foo' => 'bar', 'bazz' => 'foo');

        $this->assertSame($c, $c->configure($configs));
        $this->assertTrue(\file_exists($default), true, 'default config written');
        $this->assertSame($configs, $c->getConfig(), 'config set correct');
        $this->assertSame(parse_ini_file($default), array_change_key_case($configs, CASE_UPPER), 'correct config written');

        $this->assertSame($c, $c->configure($configs, $default));
        $this->assertSame(parse_ini_file($default), array_change_key_case($configs, CASE_UPPER), 'correct config written');

        unlink($default);
    }

    /**
     * @dataProvider provider
     */
    public function testGetSetup(Configurator $c)
    {
        $cfg = Yaml::load(__DIR__.'/../Fixtures/app/config/configuration.yml');
        $setup = $c->getSetup();
        
        $this->assertSame(count($setup), count($cfg));
    }
    
    /**
     * @dataProvider provider
     */
    public function testGrepReplaceableFiles(Configurator $c)
    {
        $method = new \ReflectionMethod(get_class($c), 'grepReplaceableFiles');
        $method->setAccessible(true);
                
        $setup = $c->getSetup();
        $files = $method->invoke($c);
        
        $this->assertSame(count($files),3);
    }
    
    /**
     * @dataProvider provider
     */
    public function testReplaceFiles(Configurator $c)
    {
        $setup = $c->getSetup();
        $c->setOutput(new NullOutput());
        $c->replaceFiles();
        $method = new \ReflectionMethod(get_class($c), 'grepReplaceableFiles');
        $method->setAccessible(true);
                
        $files = $method->invoke($c);
        
        foreach($files as $key => $file){
            $files[$key] = str_replace('.dist', '', $file);                        
        }
        
        $this->assertSame(count($files),3);
        
        foreach($files as $file){
            switch(substr($file, strpos($file,'.')+1)){
                case 'php' : 
                    $this->assertSame(file_get_contents($file),'<?php $foo = \'test\'; ?>');
                    break;
                case 'html':
                    $this->assertSame(file_get_contents($file),'<h1>test</h1>');
                    break;
                case 'yml':
                    $this->assertSame(file_get_contents($file),'foo: test');
                    break;                    
            }
        }
    }
    
    

    public function provider()
    {
        $kernel = $this->getKernel();
        
        $fs = new \Symfony\Bundle\FrameworkBundle\Util\Filesystem();
        $fs->mirror(__DIR__.'/../Fixtures/app/', $kernel->getRootDir());
        
        $c = Configurator::create($kernel);
        
        return array(
            array($c)
        );
    }

}
