<?php

//use \Phalcon\DI\FactoryDefault;

use \Disturb\Loader;
//use \Disturb\Context\Elasticsearch;

ini_set('display_errors', 1);

error_reporting(E_ALL);

require_once __DIR__ . '/../Library/Loader.php';

$loader = new Loader();

if (!isset($vendorDir)) {
    $vendorDir = realpath(__DIR__ . '/../vendor/') . '/';
}

if (!isset($testDir)) {
    $testDir = __DIR__ . '/';
}

$loader->initialize(
    $vendorDir,
    [
        'Phalcon' => $vendorDir .'/phalcon/incubator/Library/Phalcon/',
        'Tests' => $testDir
    ]
);

/*if (!isset($di)) {
    $di = new FactoryDefault();
    FactoryDefault::initialize($di);
}*/


//$elastic = new Elasticsearch();









