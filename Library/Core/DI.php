<?php

use \Phalcon\Di\FactoryDefault\Cli as CliDI;
use \Phalcon\Loader;

// Using the CLI factory default services container
$di = new CliDI();

/**
 * Register the autoloader and tell it to register the tasks directory
 */
$loader = new Loader();
$loader->registerNamespaces(
    [
        'Vpg\Disturb' => realpath(__DIR__ . '/../../Library/')
    ],
    true
);

$loader->registerFiles([__DIR__ . '/../../vendor/autoload.php']);
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
        $logger = new \Vpg\Disturb\Logger\Logger();

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

        $stdoutLogger = new \Phalcon\Logger\Adapter\Stream(
            'php://stdout'
        );
        $stdoutLogger->setFormatter(new \Vpg\Disturb\Logger\Formatter\Stream());
        $logger->push($stdoutLogger);
        return $logger;
    },
    true
);


