<?php
namespace Vpg\Disturb\Example\Test;

use \Vpg\Disturb;
use \Vpg\Disturb\Message\MessageDto as Message;

abstract class TestStepAbstract implements Disturb\Step\StepServiceInterface
{
    public function beforeExecute(array $paramHash)
    {
        echo 'before exec' . PHP_EOL;
    }

    public function afterExecute(array $paramHash, array $resultHash)
    {
        echo 'after exec' . PHP_EOL;
    }
}

