<?php

use \Vpg\Disturb\Core\Cli\Console as ConsoleApp;
use \Vpg\Disturb\Workflow\WorkflowConfigDtoFactory;

define('DISTURB_DEBUG', getenv('DISTURB_DEBUG'));
define('DISTURB_TOPIC_PREFIX', getenv('DISTURB_TOPIC_PREFIX'));
define('DISTURB_ELASTIC_HOST', getenv('DISTURB_ELASTIC_HOST'));
define('DISTURB_KAFKA_BROKER', getenv('DISTURB_KAFKA_BROKER'));

try {
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
            $arguments['task'] = $arg;
        } elseif ($k === 2) {
            $arguments['action'] = $arg;
        } elseif ($k >= 3) {
            $arguments['params'][] = $arg;
        }
    }

    // Load client boostrap file
    $paramHash = ConsoleApp::parseLongOpt(join($arguments['params'], ' '));

    $workflowConfigDto = WorkflowConfigDtoFactory::get($paramHash['workflow']);
    $projectBootstrapFilePath = $workflowConfigDto->getProjectBoostrapFilepath();
    if (is_readable($projectBootstrapFilePath)) {
        $di->get('logr')->info('Loading Bootstrap : ' . $projectBootstrapFilePath);
        require_once($projectBootstrapFilePath);
    }

    $di->get('disturb-config')->workflowConfigFilePath = $paramHash['workflow'];

    // Handle incoming arguments
    $console->handle($arguments);

} catch (\Phalcon\Exception $e) {
    // Do Phalcon related stuff here
    $di->get('logr')->error($e->getMessage());
    $di->get('logr')->error($e->getTraceAsString());
    exit(1);
} catch (\Exception $exception) {
    // Other php exception
    $di->get('logr')->error($exception->getMessage());
    $di->get('logr')->error($exception->getTraceAsString());
    exit(1);
} catch (\Throwable $throwable) {
    // run time error
    $di->get('logr')->error($throwable->getMessage());
    $di->get('logr')->error($throwable->getTraceAsString());
    exit(1);
}
