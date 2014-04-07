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

use Symfony\Component\Filesystem\Exception\FileNotFoundException;
use Symfony\Component\Console\Application as Console;
use Silex\Application;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Ten24\SilexConsoleCommandBundle\Command;
use Symfony\Component\OptionsResolver\OptionsResolver;

class CommandAssetic extends BaseCommand
{
    /**
     * Check for other application requirements before registering
     * commands
     *
     * @see \Ten24\Silex\ConsoleCommandServiceProvider\Command\BaseCommand::registerCommands()
     * @throws RuntimeException
     * @throws FileNotFoundException
     */
    protected function registerCommands()
    {
        if(!$this->app->offsetExists('assetic'))
        {
            throw new \RuntimeException('Cannot register assetic commands; configuration key "assetic" is not set.');
        }
        
        parent::registerCommands();
    }
    
    /**
     * (non-PHPdoc)
     * @see \Ten24\Silex\ConsoleCommandServiceProvider\Command\BaseCommand::configureOptions()
     */
    protected function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(array(
                'autoRegister' => false,
                'registerDump' => true
        ));
        
        $resolver->setAllowedTypes(array(
                'autoRegister' => 'bool',
                'registerDump' => 'bool'));
    }
    
    /**
     * (non-PHPdoc)
     * @see \Ten24\Silex\ConsoleCommandServiceProvider\Command\BaseCommand::setRegisterableCommands()
     */
    protected function setRegisterableCommands()
    {
        $this->commands = array(
                'dump');
    }
    
    /**
     * Registers 'assetic:dump'
     */
    public function registerDump()
    {
        $app = $this->app;
        
        $this->console->register('assetic:dump')
            ->setDescription('Dumps all assets to the filesystem')
            ->setCode(function (InputInterface $input, OutputInterface $output) use($app)
        {
            
            $dumper = $app['assetic.dumper'];
            
            if(isset($app['twig']))
            {
                $dumper->addTwigAssets();
            }
            
            $dumper->dumpAssets();
            
            $output->writeln('<info>Dump finished</info>');
        });
    }
}