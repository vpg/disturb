<?php

ini_set('display_errors', 1);

error_reporting(E_ALL);

use \Phalcon\Loader;


/**
 * Register the autoloader and tell it to register the tasks directory
 */
$loader = new Loader();
$loader->registerNamespaces(
    [
        'Phalcon' => realpath(__DIR__ . '/../vendor/phalcon/incubator/Library/Phalcon/'),
        'Vpg\Disturb' => realpath(__DIR__ . '/../Library/'),
        'Tests' => __DIR__ . '/'
    ],
    true
);

$loader->register();

$di = new Vpg\Disturb\Core\DI\Di();
require_once (__DIR__ . '/../Library/Core/DI/init.php');

$di->setShared('loader', $loader);
