<?php

namespace Tests\Helper\Workflow;

use \Vpg\Disturb\Workflow;
use \Vpg\Disturb\Message\MessageDto;


/**
 * Manager Worker test class
 *
 * @author Maxime BRENGUIER <mbrenguier@voyageprive.com>
 */
class ManagerHelper
{
    protected static $managerWorker;
    protected static $managerWorkerReflection;
    protected static $processMessageFunction;

    /**
     * ManagerHelper constructor.
     */
    public function __construct($paramHash)
    {
        // init Manager worker
        self::$managerWorker = new Workflow\ManagerWorker();
        self::$managerWorkerReflection = new \ReflectionClass(self::$managerWorker);
        $parseOtpF = self::$managerWorkerReflection->getMethod('parseOpt');
        $parseOtpF->setAccessible(true);
        $parsedOptHash = $parseOtpF->invokeArgs(self::$managerWorker, [$paramHash]);

        $parseOpt = self::$managerWorkerReflection->getProperty('paramHash');
        $parseOpt->setAccessible(true);
        $parseOpt->setValue(self::$managerWorker, $parsedOptHash);

        // init worker with params hash (workflow name)
        $initWorkerF = self::$managerWorkerReflection->getMethod('initWorker');
        $initWorkerF->setAccessible(true);
        $initWorkerF->invokeArgs(self::$managerWorker, [$paramHash]);
    }


    public function processMessage($workflowMsg) {
        $msgDto = new MessageDto($workflowMsg);
        if (!isset(self::$processMessageFunction)) {
            self::$processMessageFunction = self::$managerWorkerReflection->getMethod('processMessage');
            self::$processMessageFunction->setAccessible(true);
        }
        return self::$processMessageFunction->invokeArgs(self::$managerWorker, [$msgDto]);
    }

    public function keepItAlive() {
        $keepAliveF = self::$managerWorkerReflection->getMethod('keepItAlive');
        $keepAliveF->setAccessible(true);
        return $keepAliveF->invokeArgs(self::$managerWorker, []);
    }

}
