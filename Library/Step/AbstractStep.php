<?php
namespace Disturb\Step;

abstract class AbstractStep extends \Phalcon\Mvc\User\Component
{
    protected $MSG_ACK_SUCCESS = 'SUCCESS';
    protected $MSG_ACK_ERROR = 'ERROR';
}
