<?php

use \Phalcon\Config\Adapter\Json;

use \Vpg\Disturb\Core\Cli\Console as ConsoleApp;
use \Vpg\Disturb\Core;
use \Vpg\Disturb\Workflow;
use \Vpg\Disturb\Step;
use \Vpg\Disturb\Monitoring;

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
$loader->registerFiles([
    __DIR__ . '/../../vendor/autoload.php',
    __DIR__ . '/../../../autoload.php'
]);
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
        $arguments['host'] = $arg;
    } elseif ($k === 2) {
        $arguments['worker'] = $arg;
    } elseif ($k === 3) {
        $arguments['action'] = $arg;
    } elseif ($k >= 4) {
        $arguments['params'][] = $arg;
    }
}


$paramHash = ConsoleApp::parseLongOpt(join($arguments['params'], ' '));
$workflowConfig = new Json($paramHash['workflow']);

$projectBootstrapFilePath = $workflowConfig['projectBootstrap'] ?? '';
if (is_readable($projectBootstrapFilePath)) {
    $di->get('logr')->info('Loading Bootstrap : ' . $projectBootstrapFilePath);
    require_once($projectBootstrapFilePath);
}

switch ($arguments['worker']) {
    case 'manager':
        $workerCode = Workflow\ManagerWorker::getWorkerCode($paramHash);
    break;
    case 'step':
        $workerCode = Step\StepWorker::getWorkerCode($paramHash);
    break;
}
$monitoringService = new Monitoring\Service($workflowConfig);
switch ($arguments['action']) {
    case 'start':
        $monitoringService->logWorkerStarted($workerCode, $paramHash['pid']);
    break;
    case 'exit':
        $monitoringService->logWorkerExited($workerCode, $paramHash['exitCode']);
    break;
    case 'heartbeat':
        while (true) {
            $monitoringService->logWorkerBeat($workerCode);
            // xxx put it in conf
            sleep(5);
        }
    break;
}
