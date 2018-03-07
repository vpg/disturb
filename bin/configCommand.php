<?php

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
