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
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\Finder\Finder;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Ten24\SilexConsoleCommandBundle\Command;
use Symfony\Component\OptionsResolver\OptionsResolver;

class CommandCache extends BaseCommand
{

    /**
     * (non-PHPdoc)
     *
     * @see \Ten24\Silex\ConsoleCommandServiceProvider\Command\BaseCommand::setRegisterableCommands()
     */
    protected function setRegisterableCommands()
    {
        $this->commands = array(
                'clear');
    }

    /**
     * (non-PHPdoc)
     *
     * @see \Ten24\Silex\ConsoleCommandServiceProvider\Command\BaseCommand::configureOptions()
     */
    protected function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(array(
                'cachePath' => $this->app['cache.path'],
                'autoRegister' => false,
                'registerClear' => true));
        
        $resolver->setRequired(array(
                'cachePath'));
        
        $resolver->setAllowedTypes(array(
                'cachePath' => 'string',
                'autoRegister' => 'bool',
                'registerClear' => 'bool'));
    }

    /**
     * Registers cache:clear
     *
     * @throws \FileNotFoundException
     */
    public function registerClear()
    {
        $app = $this->app;
        
        if(!is_writeable($app['cache.path']) || !is_readable($app['cache.path']))
        {
            throw new FileNotFoundException(sprintf('Cannot register cache command: "%s" is not accessible', $app['cache.path']));
        }
        
        $this->console->register('cache:clear')
            ->setDescription('Clears the cache')
            ->setCode(function (InputInterface $input, OutputInterface $output) use($app)
        {
            $finder = Finder::create()->in($app['cache.path'])
                ->notName('.gitkeep');
            
            $filesystem = new Filesystem();
            $filesystem->remove($finder);
            
            $output->writeln(sprintf("%s <info>success</info>", 'cache:clear'));
        });
    }
}