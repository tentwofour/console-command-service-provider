console-command-service-provider
================================

A Silex Service Provider for common console commands for Cache, Doctrine DBAL, and Assetic

##Installing

Add the dependency to your composer.json

```composer
"essgeebee/console-command-service-provider"    : "dev-master"
```

###Installing the skeleton app/console and src/console.php files

*Note that these files assume a particular project structure - see the templates/console file for more details*

Add to your composer.json

```composer
"autoload": {
        "psr-0": { 
          ...
          "Ten24": "vendor/essgeebee/console-command-service-provider/src/" 
        }
    },
...    
"scripts": {
        "post-install-cmd": [ 
            "Ten24\\Composer\\ConsoleCommandInstaller::postInstallCmd"
        ],
        "post-update-cmd": [ 
            "Ten24\\Composer\\ConsoleCommandInstaller::postUpdateCmd"
        ]
    }
```

Run

```bash
composer run-script post-update-cmd
```

or 

```bash
composer run-script post-install-cmd
```

This will attempt to create the directories 'app' and 'src' at the root of your project if they don't exist, and copy templates/console and templates/console.php to app/ and src/, respectively.

From your terminal, you should now be able to run

```bash
app/console
```

and get something to this effect:

```bash
MyProject version 0.1

Usage:
  [options] command [arguments]

Options:
  --help           -h Display this help message.
  --quiet          -q Do not output any message.
  --verbose        -v|vv|vvv Increase the verbosity of messages: 1 for normal output, 2 for more verbose output and 3 for debug
  --version        -V Display this application version.
  --ansi              Force ANSI output.
  --no-ansi           Disable ANSI output.
  --no-interaction -n Do not ask any interactive question.

Available commands:
  help                       Displays help for a command
  list                       Lists commands
assetic
  assetic:dump               Dumps all assets to the filesystem
cache
  cache:clear                Clears the cache
doctrine
  doctrine:database:create   Creates the configured databases
  doctrine:database:drop     Drops the configured databases
  doctrine:schema:load       Load schema
  doctrine:schema:show       Output schema declaration
```

##Registered Commands
By default, the following commands are registered to the console:
- doctrine:schema:show
- doctrine:schema:load
- doctrine:database:create
- doctrine:database:drop
- cache:clear
- assetic:dump

##Configuration Options

The Provider looks for its options within the $app['ten24.consolecommand.options']. Here's a list of the available options - all keys within $app['ten24.consolecommand.options']. Note that the required options *do* have a default value, that may not align to your project structure.

- doctrine
    - schemaFile (required, default: $app['root.dir']./app/config/database/schema.php)
    - registerSchemaShow (optional, default: true)
    - registerSchemaLoad (optional, default: true)
    - registerDatabaseDrop (optional, default: true)
    - registerDatabaseCreate (optional, default: true)
- cache
    - cachePath (required, default: $app['root.dir']./app/cache)
    - registerClear (optional, default: true)
- assetic
    - registerDump (optional, default: true)

