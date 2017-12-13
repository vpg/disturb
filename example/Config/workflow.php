<?php

return [
    'name' => 'foo',
    'version' => '0.0.1',
    'brokerServerList' => ['localhost1', 'localhost2', 'localhost3'],
    'servicesClassPath' => '/',
    'servicesClassNameSpace' => 'Workflows\\Test',
    'projectBootstrap'=> 'bootstrap.php',
    'storage' => [
        'adapter'=> 'elasticsearch',
        'config'=> [
            'host'=> 'https://elasticsearch.localhost:443'
        ]
    ],
    'steps' => [
        [
            'name' => 'step0',
            'severity' => 'blocking',
            'type' => 'parallel'
        ],
        [
            'name' => 'step1',
            'severity' => 'blocking',
            'type' => 'serie'
        ],
        [
            'name' => 'step2',
            'severity' => 'blocking',
            'type' => 'serie'
        ],
        [
            'name' => 'step3',
            'severity' => 'blocking',
            'type' => 'serie'
        ]
    ]
];