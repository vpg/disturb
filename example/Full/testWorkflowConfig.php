<?php

include('vendor/autoload.php');

use Vpg\Disturb\Core\Cli\Console;

$format = 'php';
if (!empty($argv)) {
    $argHash = Console::parseLongOpt(implode(' ', $argv));
    $format = $argHash['format'] ?? 'php';
}

$configHash = [
    'name' => 'test',
    'version' => '0.0.1',
    'brokerServerList' => defined('DISTURB_KAFKA_BROKER') ? explode(',', DISTURB_KAFKA_BROKER) : [],
    'servicesClassPath' => './Full/',
    'servicesClassNameSpace' => 'Vpg\\Disturb\\Example\\Test',
    'storage' => [
        'adapter' => 'elasticsearch',
        'config' => [
            'host' => defined('DISTURB_ELASTIC_HOST') ? DISTURB_ELASTIC_HOST : ''
        ]
    ],
    'steps' => [
        [
            'name' => 'start',
            'instances' => 3
        ],
        [
            'name' => 'bar'
        ],
        [
            'name' => 'foo'
        ],
        [
            [
                'name' => 'far',
                'instances' => 3
            ],
            [
                'name' => 'boo'
            ]
        ],
        [
            'name' => 'end'
        ]
    ]
];

switch ($format) {
    case 'php';
        return $configHash;
        break;
    case 'json' :
        echo json_encode($configHash);
        break;
    default:
        throw new \Exception('Unauthorized config format');
}