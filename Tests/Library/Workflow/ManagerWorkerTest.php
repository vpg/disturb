<?php

namespace Tests\Library\Context;

use \phalcon\Config;
use \Vpg\Disturb\Workflow;
use Vpg\Disturb\Message\MessageDto;
use \Vpg\Disturb\Context\ContextStorageService;
use \Vpg\Disturb\Workflow\WorkflowConfigDtoFactory;


/**
 * Manager Worker test class
 *
 * @author JEROME BOURGEAIS <jbourgeais@voyageprive.com>
 */
class ManagerWorkerTest extends \Tests\DisturbUnitTestCase
{

    protected static $workflowSerieConfigDto;
    protected static $workerParamHash;
    protected static $contextStorageService;
    protected $managerWorker;

    protected $workerHostname = 'worker-test-hostname';

    /**
     * Setup
     *
     * @return void
     */
    public static function setUpBeforeClass()
    {
        parent::setUpBeforeClass();
        $configFilpath = realpath(__DIR__ . '/../../Config/serie.json');
        self::$workflowSerieConfigDto = WorkflowConfigDtoFactory::get($configFilpath);
        self::$contextStorageService = new ContextStorageService(self::$workflowSerieConfigDto);
        self::$workerParamHash = [
           "--workflow=$configFilpath"
        ];
    }

    /**
     * returns a new wfid
     *
     * @return string a brand new wf id
     */
    private function generateWfId(): string
    {
        return str_replace(' ', '', 'test' . microtime());
    }

    /**
     * Test init()
     *
     * @return void
     */
    public function testStartWorkflow()
    {
        $managerWorker = new Workflow\ManagerWorker();
        $managerWorkerReflection = new \ReflectionClass($managerWorker);
        $parseOtpF = $managerWorkerReflection->getMethod('parseOpt');
        $parseOtpF->setAccessible(true);
        $parsedOptHash = $parseOtpF->invokeArgs($managerWorker, [self::$workerParamHash]);

        $parseOpt = $managerWorkerReflection->getProperty('paramHash');
        $parseOpt->setAccessible(true);
        $parseOpt->setValue($managerWorker, $parsedOptHash);

        $initWorkerF = $managerWorkerReflection->getMethod('initWorker');
        $initWorkerF->setAccessible(true);
        $initWorkerF->invokeArgs($managerWorker, [self::$workerParamHash]);

        $wfId = $this->generateWfId();
        $startWFMsg = '{"id":"' . $wfId . '", "type" : "WF-CONTROL", "action":"start", "payload": {"foo":"bar"}}';
        $msgDto = new MessageDto($startWFMsg);

        $processMessageF = $managerWorkerReflection->getMethod('processMessage');
        $processMessageF->setAccessible(true);
        $processMessageF->invokeArgs($managerWorker, [$msgDto]);

        $wfDto = self::$contextStorageService->get($wfId);

        $wfStatus = $wfDto->getWorkflowStatus();
        $this->assertEquals(
            Workflow\ManagerService::STATUS_STARTED,
            $wfStatus
        );
        $initialPayloadHash = $wfDto->getInitialPayload();
        $this->assertEquals(
            [
                'foo' => 'bar',
            ],
            $initialPayloadHash
        );
    }

    /**
     * Test init()
     *
     * @return void
     */
    public function testBadInitClientClass()
    {
        $configFilpath = realpath(__DIR__ . '/../../Config/serieWrongClientClass.json');
        $workerParamHash = [
           "--workflow=$configFilpath"
        ];
        $managerWorker = new Workflow\ManagerWorker();
        $managerWorkerReflection = new \ReflectionClass($managerWorker);
        $parseOtpF = $managerWorkerReflection->getMethod('parseOpt');
        $parseOtpF->setAccessible(true);
        $parsedOptHash = $parseOtpF->invokeArgs($managerWorker, [$workerParamHash]);

        $parseOpt = $managerWorkerReflection->getProperty('paramHash');
        $parseOpt->setAccessible(true);
        $parseOpt->setValue($managerWorker, $parsedOptHash);

        $initWorkerF = $managerWorkerReflection->getMethod('initWorker');
        $parsedOptHash = $initWorkerF->setAccessible(true);
        $this->expectException(Workflow\WorkflowException::class);
        $initWorkerF->invokeArgs($managerWorker, [self::$workerParamHash]);

    }
}
