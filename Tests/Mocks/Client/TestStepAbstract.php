<?php
namespace Vpg\Disturb\Test;

use \Vpg\Disturb;
use \Vpg\Disturb\Message\MessageDto as Message;

abstract class TestStepAbstract implements Disturb\Step\StepServiceInterface
{
    public function beforeExecute(array $paramHash)
    {
    }

    public function afterExecute(array $paramHash, array $resultHash)
    {
    }
}

