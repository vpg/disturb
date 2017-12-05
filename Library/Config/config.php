<?php

$configHash = [];

$config = new \Phalcon\Config($configHash);
$di->set('disturb-config', $config);
