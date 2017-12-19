<?php

// Using the CLI factory default services container
$di = new \Phalcon\Di\FactoryDefault\Cli();


// Load the configuration file (if any)
$configFile = __DIR__ . '/../Config/config.php';

if (is_readable($configFile)) {
    require_once($configFile);
}

$di->set('dispatcher', function () {
    $dispatcher = new \Phalcon\Cli\Dispatcher();
    $dispatcher->setTaskSuffix('Worker');

    return $dispatcher;
});

// Register logger
$di->set(
    'logr',
    function () use ($di) {
        $logger = new \Vpg\Disturb\Core\Logger\Logger();
        $stdoutLogger = new \Phalcon\Logger\Adapter\Stream(
            'php://stdout'
        );
        $stdoutLogger->setFormatter(new \Vpg\Disturb\Core\Logger\Formatter\Stream());
        $logger->push($stdoutLogger);
        return $logger;
    },
    true
);

\Phalcon\Di::setDefault($di);
