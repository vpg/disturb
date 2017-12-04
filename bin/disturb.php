<?php

use \Phalcon\Config\Adapter\Json;

use \Vpg\Disturb\Core\Cli\Console as ConsoleApp;

define('DISTURB_DEBUG', getenv('DISTURB_DEBUG'));
define('DISTURB_TOPIC_PREFIX', getenv('DISTURB_TOPIC_PREFIX'));

/**
 * Register the autoloader and tell it to register the tasks directory
 */
$loader = new \Phalcon\Loader();
$loader->registerNamespaces(
    [
        'Vpg\Disturb' => realpath(__DIR__ . '/../Library/')
    ],
    true
);

$loader->registerFiles([__DIR__ . '/../../vendor/autoload.php']);
$loader->register();

require_once(__DIR__ . '/../Library/Core/DI.php');

$di->setShared('loader', $loader);


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

// Load client boostrap file
$paramHash = ConsoleApp::parseLongOpt(join($arguments['params'], ' '));
$workflowConfig = new Json($paramHash['workflow']);
$projectBootstrapFilePath = $workflowConfig['projectBootstrap'] ?? '';
if (is_readable($projectBootstrapFilePath)) {
    $di->get('logr')->info('Loading Bootstrap : ' . $projectBootstrapFilePath);
    require_once($projectBootstrapFilePath);
}
$di->get('config')->workflowConfigFilePath = $paramHash['workflow'];

try {
    // Handle incoming arguments
    $console->handle($arguments);
} catch (\Phalcon\Exception $e) {
    // Do Phalcon related stuff here
    fwrite(STDERR, $e->getMessage() . PHP_EOL);
    exit(1);
} catch (\Throwable $throwable) {
    fwrite(STDERR, $throwable->getMessage() . PHP_EOL);
    exit(1);
} catch (\Exception $exception) {
    fwrite(STDERR, $exception->getMessage() . PHP_EOL);
    exit(1);
}
