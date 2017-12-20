<?php
namespace Vpg\Disturb\Example\Test;

use \Vpg\Disturb;
use \Vpg\Disturb\Message\MessageDto as Message;

class BarStep extends TestStepAbstract
{
    public function execute(array $paramHash) : array {
        $resultHash = [];
        echo  "Exec". PHP_EOL;
        for($i=0; $i<rand(1,10); $i++) {
            echo '.';
            sleep(1);
        }
        echo PHP_EOL . "Done" . PHP_EOL;
        $resultHash = [
            'status' => Message::MSG_RETURN_SUCCESS,
            'data' => [ 'foo' => get_class($this), 'bar' => rand(1,10)]
        ];
        return $resultHash;
    }
}

