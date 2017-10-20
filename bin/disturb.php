<?php

use Phalcon\Di\FactoryDefault\Cli as CliDI;
use Phalcon\Cli\Console as ConsoleApp;
use Phalcon\Loader;


// Using the CLI factory default services container
$di = new CliDI();

/**
 * Register the autoloader and tell it to register the tasks directory
 */
$loader = new Loader();
$loader->registerNamespaces(
    [
        'Disturb' => realpath(__DIR__ . '/../Library/')
    ],
    true
);

$loader->register();
$di->setShared('loader', $loader);

// Load the configuration file (if any)
$configFile = __DIR__ . '/Config/config.php';

if (is_readable($configFile)) {
    $config = include $configFile;

    $di->set('config', $config);
}

// Register logger
$di->set(
    'logger',
    function () use ($di) {
        $logger = new \Disturb\Logger\Logger();

        // xxx - syslog
        /*$syslog = new \Phalcon\Logger\Adapter\Syslog(
            xxx - $logName,
            [
                'option' => LOG_NDELAY,
                'facility' => LOG_LOCAL1
            ]
        );
        $syslog->setFormatter(new \Disturb\Logger\Formatter\Syslog());
        $syslog->setLogLevel(LOG_INFO);
        $logger->push($syslog);*/

        $phplog = new \Phalcon\Logger\Adapter\Stream(
            'php://stdout'
        );
        $phplog->setFormatter(new \Disturb\Logger\Formatter\Stream());
        $logger->push($phplog);
        return $logger;
    },
    true
);

// Create a console application
$console = new ConsoleApp();
$console->setDI($di);


/**
 * Process the console arguments
 */
$arguments = [];

foreach ($argv as $k => $arg) {
    if ($k === 1) {
        $arguments['task'] = $arg;
    } elseif ($k === 2) {
        $arguments['action'] = $arg;
    } elseif ($k >= 3) {
        $arguments['params'][] = $arg;
    }
}

try {
    // Handle incoming arguments
    $console->handle($arguments);
} catch (\Phalcon\Exception $e) {
    // Do Phalcon related stuff here
    // ..
    fwrite(STDERR, $e->getMessage() . PHP_EOL);
    exit(1);
} catch (\Throwable $throwable) {
    fwrite(STDERR, $throwable->getMessage() . PHP_EOL);
    exit(1);
} catch (\Exception $exception) {
    fwrite(STDERR, $exception->getMessage() . PHP_EOL);
    exit(1);
}
