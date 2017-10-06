<?php
namespace Ex\Services;

abstract class AbstractTask extends \Phalcon\Mvc\User\Component
{
    protected $taskOptionBaseList = [
        'workflow:',  // required step code config file
    ];

    protected $MSG_ACK_SUCCESS = 'SUCCESS';
    protected $MSG_ACK_ERROR = 'ERROR';
}
