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

use Doctrine\DBAL\DriverManager;
use Symfony\Component\Console\Application as Console;
use Silex\Application;
use Symfony\Component\Filesystem\Exception\FileNotFoundException;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Doctrine\DBAL\DBALException;

class CommandDoctrine extends BaseCommand
{

    /**
     * Check for other application requirements before registering
     * commands
     * 
     * @see \Ten24\Silex\ConsoleCommandServiceProvider\Command\BaseCommand::registerCommands()
     * @throws DBALException
     * @throws FileNotFoundException
     */
    protected function registerCommands()
    {
        if(!$this->app->offsetExists('db'))
        {
            throw new DBALException('$app[\'db\'] is not defined.');
        }
        
        if(!is_readable($this->options['schemaFile']) | !is_file($this->options['schemaFile']))
        {
            throw new FileNotFoundException(sprintf('Cannot locate schema file; looked in "%s".', $this->options['schemaFile']));
        }
        
        parent::registerCommands();
    }

    /**
     * Configure options
     *
     * @see \Ten24\Silex\ConsoleCommandServiceProvider\Command\BaseCommand::configureOptions()
     * @param OptionsResolverInterface $resolver            
     */
    protected function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(array(
                'autoRegister' => true,
                'registerSchemaShow' => true,
                'registerSchemaLoad' => true,
                'registerDatabaseCreate' => true,
                'registerDatabaseDrop' => true,
                'schemaFile' => $this->app['root.dir'] . '/app/config/database/schema.php'));
        
        $resolver->setRequired(array(
                'schemaFile'));
        
        $resolver->setAllowedTypes(array(
                'autoRegister' => 'bool',
                'registerSchemaShow' => 'bool',
                'registerSchemaLoad' => 'bool',
                'registerDatabaseCreate' => 'bool',
                'registerDatabaseDrop' => 'bool',
                'schemaFile' => 'string'));
    }

    /**
     * (non-PHPdoc)
     * @see \Ten24\Silex\ConsoleCommandServiceProvider\Command\BaseCommand::setRegisterableCommands()
     */
    protected function setRegisterableCommands()
    {
        $this->commands = array(
                'schema:show',
                'schema:load',
                'database:create',
                'database:drop');
    }

    /**
     * Shortcut to register all commands in the doctrine namespace
     */
    public function registerAll()
    {
        $this->registerCommands();
    }

    /**
     * Register doctrine:schema:show
     */
    public function registerSchemaShow()
    {
        $app = $this->app;
        
        $this->console->register('doctrine:schema:show')
            ->setDescription('Output schema declaration')
            ->setCode(function (InputInterface $input, OutputInterface $output) use($app)
        {
            $schema = require $this->options['schemaFile'];
            
            foreach($schema->toSql($this->app['db']->getDatabasePlatform()) as $sql)
            {
                $output->writeln($sql . ';');
            }
        });
    }

    /**
     * Register doctrine:schema:load
     */
    public function registerSchemaLoad()
    {
        $app = $this->app;
        
        $this->console->register('doctrine:schema:load')
            ->setDescription('Load schema')
            ->setCode(function (InputInterface $input, OutputInterface $output) use($app)
        {
            $schema = require $this->options['schemaFile'];
            
            foreach($schema->toSql($app['db']->getDatabasePlatform()) as $sql)
            {
                $app['db']->exec($sql . ';');
            }
        });
    }

    /**
     * Register doctrine:database:drop
     *
     * @throws \InvalidArgumentException
     * @return number
     */
    public function registerDatabaseDrop()
    {
        $app = $this->app;
        
        $this->console->register('doctrine:database:drop')
            ->setName('doctrine:database:drop')
            ->setDescription('Drops the configured databases')
            ->addOption('connection', null, InputOption::VALUE_OPTIONAL, 'The connection to use for this command')
            ->addOption('force', null, InputOption::VALUE_NONE, 'Set this parameter to execute this action')
            ->setHelp(<<<EOT
The <info>doctrine:database:drop</info> command drops the default connections
database:

<info>php app/console doctrine:database:drop</info>

The --force parameter has to be used to actually drop the database.

You can also optionally specify the name of a connection to drop the database
for:

<info>php app/console doctrine:database:drop --connection=default</info>

<error>Be careful: All data in a given database will be lost when executing
this command.</error>
EOT
        )
            ->setCode(function (InputInterface $input, OutputInterface $output) use($app)
        {
            $connection = $app['db'];
            
            $params = $connection->getParams();
            
            $name = isset($params['path']) ? $params['path'] : (isset($params['dbname']) ? $params['dbname'] : false);
            
            if(!$name)
            {
                throw new \InvalidArgumentException("Connection does not contain a 'path' or 'dbname' parameter and cannot be dropped.");
            }
            
            if($input->getOption('force'))
            {
                // Only quote if we don't have a path
                if(!isset($params['path']))
                {
                    $name = $connection->getDatabasePlatform()
                        ->quoteSingleIdentifier($name);
                }
                
                try
                {
                    $connection->getSchemaManager()
                        ->dropDatabase($name);
                    $output->writeln(sprintf('<info>Dropped database for connection named <comment>%s</comment></info>', $name));
                }
                catch(\Exception $e)
                {
                    $output->writeln(sprintf('<error>Could not drop database for connection named <comment>%s</comment></error>', $name));
                    $output->writeln(sprintf('<error>%s</error>', $e->getMessage()));
                    
                    return 1;
                }
            }
            else
            {
                $output->writeln('<error>ATTENTION:</error> This operation should not be executed in a production environment.');
                $output->writeln('');
                $output->writeln(sprintf('<info>Would drop the database named <comment>%s</comment>.</info>', $name));
                $output->writeln('Please run the operation with --force to execute');
                $output->writeln('<error>All data will be lost!</error>');
                
                return 2;
            }
        });
    }

    /**
     * Register doctrine:database:create
     *
     * @return number
     */
    public function registerDatabaseCreate()
    {
        $app = $this->app;
        
        $this->console->register('doctrine:database:create')
            ->setDescription('Creates the configured databases')
            ->addOption('connection', null, InputOption::VALUE_OPTIONAL, 'The connection to use for this command')
            ->setHelp(<<<EOT
The <info>doctrine:database:create</info> command creates the default
connections database:

<info>php app/console doctrine:database:create</info>

You can also optionally specify the name of a connection to create the
database for:

<info>php app/console doctrine:database:create --connection=default</info>
EOT
        )
            ->setCode(function (InputInterface $input, OutputInterface $output) use($app)
        {
            $connection = $app['db'];
            
            $params = $connection->getParams();
            $name = isset($params['path']) ? $params['path'] : $params['dbname'];
            
            unset($params['dbname']);
            
            $tmpConnection = DriverManager::getConnection($params);
            
            // Only quote if we don't have a path
            if(!isset($params['path']))
            {
                $name = $tmpConnection->getDatabasePlatform()
                    ->quoteSingleIdentifier($name);
            }
            
            $error = false;
            try
            {
                $tmpConnection->getSchemaManager()
                    ->createDatabase($name);
                $output->writeln(sprintf('<info>Created database for connection named <comment>%s</comment></info>', $name));
            }
            catch(\Exception $e)
            {
                $output->writeln(sprintf('<error>Could not create database for connection named <comment>%s</comment></error>', $name));
                $output->writeln(sprintf('<error>%s</error>', $e->getMessage()));
                $error = true;
            }
            
            $tmpConnection->close();
            
            return $error ? 1 : 0;
        });
    }
}