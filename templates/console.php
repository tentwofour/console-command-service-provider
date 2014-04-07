<?php
/**
 * This file required by app/console and allows you to customize the
 * console in your project.
 *
 * $console MUST be returned
 */
use Symfony\Component\Console\Application;
use Ten24\Silex\ConsoleCommandServiceProvider\ConsoleCommandServiceProvider;

/**
 * Set console name and version
 * 
 * @var Symfony\Component\Console\Application
 * @see app/console
 */
$console->setName('MemFault');
$console->setVersion('');

/**
 * Register the ConsoleCommandServiceProvider
 * Options can be set in $app['ten24.consolecommand.options']
 * 
 * @see Ten24\Silex\ConsoleCommandServiceProvider\ConsoleCommandServiceProvider
 *      for available options
 */
$app->register(new ConsoleCommandServiceProvider($app));

// Bootstrap the application
$app->boot();

// Return to app/console for the $console->run() invokation
return $console;