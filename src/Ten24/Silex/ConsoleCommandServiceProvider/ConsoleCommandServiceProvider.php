<?php

/**
 * This file is part of ConsoleCommandServiceProvider.
 *
 * For the full copyright and license information, please view the
 * LICENSE file that was distributed with this source code.
 *
 * @author blair <blair@tentwentyfour.ca>
 */
namespace Ten24\Silex\ConsoleCommandServiceProvider;

use Silex\Application;
use Silex\ServiceProviderInterface;
use Ten24\Silex\ConsoleCommandServiceProvider\Command;

class ConsoleCommandServiceProvider implements ServiceProviderInterface
{

    /**
     * Register this service
     * (non-PHPdoc)
     *
     * @see \Silex\ServiceProviderInterface::register()
     */
    public function register(Application $app)
    {
        $console = $app['console'];
        
        $app['ten24.consolecommand.default_options'] = array(
                'doctrine' => array(
                        'autoRegister' => true,
                        'registerSchemaShow' => true,
                        'registerSchemaLoad' => true,
                        'registerDatabaseDrop' => true,
                        'registerDatabaseCreate' => true,
                        'schemaFile' => $app['root.dir'] . '/app/config/database/schema.php'),
                'cache' => array(
                        'autoRegister' => true,
                        'registerClear' => true,
                        'cachePath' => $app['cache.path']),
                'assetic' => array(
                        'autoRegister' => true,
                        'registerDump' => true));
        
        if(!$app->offsetExists('ten24.consolecommand.options'))
        {
            $app['ten24.consolecommand.options'] = $app['ten24.consolecommand.default_options'];
        }
        else
        {
            $app['ten24.consolecommand.options'] = array_merge($app['ten24.consolecommand.options'], $app['ten24.consolecommand.default_options']);
        }
        
        // Register doctrine:* console commands
        $doctrine = new Command\CommandDoctrine($console, $app, $app['ten24.consolecommand.options']['doctrine']);
        
        // Register assetic:* console commands
        $assetic = new Command\CommandAssetic($console, $app, $app['ten24.consolecommand.options']['assetic']);
        
        // Register cache:* commands
        $cache = new Command\CommandCache($console, $app, $app['ten24.consolecommand.options']['cache']);
    }

    /**
     * Bootstrap the application
     * (non-PHPdoc)
     *
     * @see \Silex\ServiceProviderInterface::boot()
     */
    public function boot(Application $app)
    {
    }
}