<?php

/**
 * This file is part of ConsoleCommandServiceProvider.
 *
 * For the full copyright and license information, please view the
 * LICENSE file that was distributed with this source code.
 *
 * @author blair <blair@tentwentyfour.ca>
 */
namespace Ten24\Silex\ConsoleCommandServiceProvider\Command;

use Silex\Application;
use Symfony\Component\Console\Application as Console;
use Symfony\Component\OptionsResolver\OptionsResolver;

abstract class BaseCommand
{

    /**
     *
     * @var Silex\Application
     */
    protected $app = null;

    /**
     *
     * @var Symfony\Component\Console\Application
     */
    protected $console = null;

    /**
     * Options
     * 
     * @var array
     */
    protected $options;

    /**
     * The classes available commands (schema:show, database:drop,
     * cache:clear, etc)
     * 
     * @var array
     */
    protected $commands;

    /**
     *
     * @var unknown
     */
    protected $resolver;

    /**
     * Constructor
     * @param Application $app            
     * @throws \Exception
     */
    public function __construct(Console $console, Application $app, array $params = array())
    {
        $this->app = $app;
        $this->console = $console;
        
        $this->resolver = new OptionsResolver();
        $this->configureOptions($this->resolver);
        $this->options = $this->resolver->resolve($params);
        
        $this->setRegisterableCommands();
        
        if($this->options['autoRegister'])
        {
            $this->registerCommands();
        }
    }

    /**
     * Configure options
     * 
     * @param OptionsResolver $resolver            
     */
    abstract protected function configureOptions(OptionsResolver $resolver);

    /**
     * Configure the commands that can be registered
     */
    abstract protected function setRegisterableCommands();

    /**
     * Register commands - called from __construct if
     * $options['autoRegister'] is true
     * 
     * @return void
     */
    protected function registerCommands()
    {
        foreach($this->commands as $command)
        {
            $method = $command;
            
            // unnecessary, methods aren't case-sensitive...yet...
            if(preg_match_all('/([:\-](\w{1}))?/', $command, $matches))
            {
                $ret = $matches[0];
                
                foreach($ret as $key => &$match)
                {
                    $method = str_replace($match, strtoupper($matches[2][$key]), $method);
                }
                
                $method = 'register' . ucfirst($method);
                
                if(method_exists($this, $method) && $this->options[$method])
                {
                    $this->$method();
                }
            }
        }
    }
}