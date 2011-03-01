<?php

namespace rs\ProjectUtilitiesBundle\Project;

use Symfony\Component\HttpKernel\KernelInterface;
use Symfony\Component\Yaml\Yaml;
use Symfony\Bundle\FrameworkBundle\Util\Filesystem;
use Symfony\Component\Finder\Finder;
use Symfony\Component\Console\Output\OutputInterface;
use \InvalidArgumentException;

/**
 * the configurator configures your application with serveral defined variables
 * it replaces in files and dirs
 * 
 * %%FOO_VAR%% will be replaced with a defined variables
 * you define them in your homedir ~/.my-secret-confs_dev.ini
 * 
 * @author Robert Schönthal <seroscho@googlemail.com>
 * @package rs.ProjectUtitlitiesBundle
 * @subpackage Project
 */
class Configurator
{    
    protected   $private = array(),
                $setup = array(),
                $kernel,
                $dist,
                $defaults,
                $filesystem,
                $output,
                $file
    ;
    
    /**
     * shortcut for instanciation
     * 
     * @param KernelInterace $kernel
     * @param string $file
     * @return Configurator
     */
    public static function create(KernelInterface $kernel, $file = null)
    {
        $instance = new self();        
        $instance->setKernel($kernel);
        
        if($file){
            $instance->loadConfig($file);
        }
        
        return $instance;
    }
    
    /**
     * injects a kernel
     * 
     * @param KernelInterface $kernel
     * @return Configurator 
     */
    public function setKernel(KernelInterface $kernel)
    {
        $this->kernel = $kernel;
        $this->dist = $kernel->getContainer()->getParameter('configurator.dist');
        $method = new \ReflectionMethod(get_class($kernel), 'getKernelParameters');
        $method->setAccessible(true);
        $this->defaults = $method->invoke($this->kernel);
        
        return $this;
    }
    
    /**
     * returns the current configurable variables
     * 
     * @return array
     */
    public function getConfig()
    {
        if(!$this->private && file_exists($this->getDefaultConfigFile())){
            //default
            $this->loadConfig($this->getDefaultConfigFile());
        }
        
        return $this->private;
    }
    
    /**
     * injects a filesystem
     * 
     * @param Filesystem $filesystem
     * @return Configurator 
     */
    public function setFilesystem(Filesystem $filesystem)
    {
        $this->filesystem = $filesystem;
        
        return $this;
    }
    
    /**
     * injects an output
     * 
     * @param OutputInterface $output
     * @return Configurator 
     */
    public function setOutput(OutputInterface $output)
    {
        $this->output = $output;
        
        return $this;
    }
    
    /**
     * logs messages to output if available
     * 
     * @param string $msg 
     */
    public function log($msg)
    {
        if($this->output){
            $this->output->writeln($msg);
        }
    }
    
    /**
     * returns the filesystem
     * 
     * @return Filesystem 
     */
    protected function getFilesystem()
    {
        if(!$this->filesystem){
            $this->filesystem = new Filesystem();
        }
        
        return $this->filesystem;
    }
    
    /**
     * loads config vars from a file
     * 
     * @param string $file
     * @return Configurator 
     */
    public function loadConfig($file)
    {
        if(!file_exists($file)){
            throw new InvalidArgumentException('file not found '.$file);
        }
        
        $this->private = array_change_key_case(parse_ini_file($file),\CASE_UPPER);
        $this->file = $file;
        
        return $this;
    }
        
    protected function getDefaultConfigFile()
    {
        $name = $this->kernel->getName() ? $this->kernel->getName() : substr($this->kernel->getRootDir(), strripos($this->kernel->getRootDir(), \DIRECTORY_SEPARATOR)+1);
        
        return sprintf('%s.%s_%s.ini',getenv('HOME').DIRECTORY_SEPARATOR,$name ,$this->kernel->getEnvironment());
    }
    
    /**
     * writes the config vars to an ini file
     * 
     * @param string $file
     * @return Configurator 
     */
    public function writeConfig($file = null)
    {
        if(!$file || (!$file && !$this->file)){
            $this->file = $this->getDefaultConfigFile();
        }
        
        if(!file_exists($this->file)){
            touch($this->file);
        }
        
        $this->write_ini_file(array_change_key_case($this->private,CASE_UPPER), $this->file);        
        
        return $this;
    }
    
    /**
     * loads the config vars and writes the config back to a private file
     * 
     * @param type $file
     * @param type $configs
     * @return Configurator 
     */
    public function configure($configs = array(), $file = null)
    {
        $this->readSetup();
        
        if($file){
            $this->loadConfig($file);
        }else{
            //load from defaults
            if(!file_exists($this->getDefaultConfigFile())){
                $this->writeConfig();
            }
            $this->loadConfig($this->getDefaultConfigFile());
        }
        
        $this->private = array_merge($this->private,$configs);
        
        $this->writeConfig($file);
        $this->replaceFiles();
        
        return $this;
    }
    
    /**
     * returns the current setup
     * 
     * @return type 
     */
    public function getSetup()
    {
        return $this->setup ? $this->setup : $this->readSetup();
    }    
    
    protected function readSetup()
    {
        $file = $this->kernel->getContainer()->getParameter('configurator.setup'); 
        $this->setup = array_merge(array(
            'in_dirs' => array(),
            'in_files' => array(),
            'variables' => array()
        ),Yaml::load($file));
        
        $this->setup = array_map(array($this,'replaceTokensInArray'), $this->setup);
        
        return $this->setup;
    }
    
    /**
     * replace variables in files
     */
    public function replaceFiles()
    {
        $files = $this->grepReplaceableFiles();
        
        foreach ($files as $key => $file) {
            //cut off .dist, and make a copy
            $this->getFilesystem()->copy($file, substr($file, 0, -strlen($this->dist)), array('override' => true));
            $files[$key] = substr($file, 0, -strlen($this->dist));
            $this->log('<info>file+</info> '.$files[$key]);
        }

        $this->replaceTokensInFiles($files);
    }
    
    protected function grepReplaceableFiles()
    {        
        //replace possible config vars in setup file
        $this->setup['in_dirs'] = array_map(array($this,'formatPath'),$this->setup['in_dirs']);
        $this->setup['in_files'] = array_map(array($this,'formatPath'),$this->setup['in_files']);
        
        $_files = array();
        
        if($this->setup['in_files']){
            $finder = new Finder();
            $files = $finder->
                    files()->
                    name('*'.$this->dist)->
                    exclude('vendor')->
                    ignoreVCS(true)->
                    in((array) $this->setup['in_dirs'])->
                    getIterator();
            
            foreach($files as $file){
                $_files[] = $file->getPathName();
            }
        }
        
        foreach((isset($this->setup['in_files']) ? $this->setup['in_files'] : array()) as $file){
            if(file_exists($file)){
                $_files[] = $file;
            }
        }

        return $_files;
    }
    
    protected function formatPath($cfg)
    {
        if(strpos($cfg,'/') !== 0){
            $cfg = $this->kernel->getRootDir().'/'.$cfg;
        }
        
        return $this->replaceTokensInString($cfg, '%%', $this->defaults);
    }

    protected function replaceTokensInFiles($files = array(), $tokens = array())
    {
        $tokens = array_merge($tokens, $this->private, $this->defaults);

        foreach($files as $file){
            $content = file_get_contents($file);
            $content = $this->replaceTokensInString($content, '%%', $tokens);
            file_put_contents($file, $content);
        }
    }
    
    protected function replaceTokensInArray($array)
    {
        foreach($array as $key =>$val){
            if(is_array($val)){
                $val = $this->replaceTokensInArray($val);
            }
            $array[$key] = $this->replaceTokensInString($val, '%%', $this->defaults);
        }
        
        return $array;
    }

    /**
     * replaces vars within a string
     *
     * @param string $str
     * @param string $holder
     * @param array $set
     * @return string
     */
    protected function replaceTokensInString($str, $holder='%%', $set=array())
    {
        if (!is_string($str) || strpos($str, $holder) === false) {
            return $str;
        }

        foreach ($set as $_key => $_value) {
            if (strpos($str, $holder . strtoupper($_key) . $holder) !== false) {
                $str = str_replace($holder . strtoupper($_key) . $holder, $_value, $str);
            }
        }

        return $str;
    }

    /**
     * writes an ini file
     * 
     * @see http://stackoverflow.com/questions/1268378/create-ini-file-write-values-in-php
     * @todo replace with a more sophisticated method
     * 
     * @param type $assoc_arr
     * @param type $path
     * @param type $has_sections
     * @return type 
     */
    private function write_ini_file($assoc_arr, $path, $has_sections=FALSE)
    {
        $content = "";
        if ($has_sections) {
            foreach ($assoc_arr as $key => $elem) {
                $content .= "[" . $key . "]\n";
                foreach ($elem as $key2 => $elem2) {
                    if (is_array($elem2)) {
                        for ($i = 0; $i < count($elem2); $i++) {
                            $content .= $key2 . "[] = \"" . $elem2[$i] . "\"\n";
                        }
                    } else if ($elem2 == "")
                        $content .= $key2 . " = \n";
                    else
                        $content .= $key2 . " = \"" . $elem2 . "\"\n";
                }
            }
        }
        else {
            foreach ($assoc_arr as $key => $elem) {
                if (is_array($elem)) {
                    for ($i = 0; $i < count($elem); $i++) {
                        $content .= $key . "[] = \"" . $elem[$i] . "\"\n";
                    }
                } else if ($elem == "")
                    $content .= $key . " = \n";
                else
                    $content .= $key . " = \"" . $elem . "\"\n";
            }
        }

        if (!$handle = fopen($path, 'w')) {
            return false;
        }
        if (!fwrite($handle, $content)) {
            return false;
        }
        fclose($handle);
        return true;
    }

}
