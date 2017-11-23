<?php

// Using the CLI factory default services container
$di = new \Phalcon\Di\FactoryDefault\Cli();


// Load the configuration file (if any)
$configFile = __DIR__ . '/Config/config.php';

if (is_readable($configFile)) {
    $config = include $configFile;

    $di->set('config', $config);
}

$di->set('dispatcher', function () {
    $dispatcher = new \Phalcon\Cli\Dispatcher();
    $dispatcher->setTaskSuffix('Worker');

    return $dispatcher;
});

// Register logger
$di->set(
    'logger',
    function () use ($di) {
        $logger = new \Vpg\Disturb\Core\Logger\Logger();

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
        $stdoutLogger->setFormatter(new \Vpg\Disturb\Core\Logger\Formatter\Stream());
        $logger->push($stdoutLogger);
        return $logger;
    },
    true
);

\Phalcon\Di::setDefault($di);
