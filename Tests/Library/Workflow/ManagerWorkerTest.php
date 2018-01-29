<?php

namespace Tests\Library\Workflow;

use \phalcon\Config;
use \Vpg\Disturb\Core\Worker;
use \Vpg\Disturb\Workflow;
use \Vpg\Disturb\Message\MessageDto;
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
        $configFilepath = realpath(__DIR__ . '/../../Config/serie.json');
        self::$workflowSerieConfigDto = WorkflowConfigDtoFactory::get($configFilepath);
        self::$contextStorageService = new ContextStorageService(self::$workflowSerieConfigDto);
        self::$workerParamHash = [
           "--workflow=$configFilepath"
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
     * Test start workflow
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
     * Test a same workflow cannot be sent more than once
     *
     * @return void
     */
    public function testStartWorkflowTwice()
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
        $processed = $processMessageF->invokeArgs($managerWorker, [$msgDto]);
        $this->assertTrue($processed);

        $processed = $processMessageF->invokeArgs($managerWorker, [$msgDto]);
        $this->assertFalse($processed);
    }

    /**
     * Test init()
     *
     * @return void
     */
    public function testBadInitClientParams()
    {
        $configFilepath = realpath(__DIR__ . '/../../Config/serieWrongClientClass.json');
        $workerParamHash = [
        ];
        $managerWorker = new Workflow\ManagerWorker();
        $managerWorkerReflection = new \ReflectionClass($managerWorker);
        $parseOtpF = $managerWorkerReflection->getMethod('parseOpt');
        $parseOtpF->setAccessible(true);
        $this->expectException(Worker\WorkerException::class);
        $parsedOptHash = $parseOtpF->invokeArgs($managerWorker, [$workerParamHash]);
    }

    /**
     * Test init()
     *
     * @return void
     */
    public function testBadInitClientClass()
    {
        $configFilepath = realpath(__DIR__ . '/../../Config/serieWrongClientClass.json');
        $workerParamHash = [
           "--workflow=$configFilepath"
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

    /**
     * Test ttl
     *
     * @return void
     */
    public function testKeepAliveNoTTL()
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
        $initWorkerF->invokeArgs($managerWorker, []);

        $keepAliveF = $managerWorkerReflection->getMethod('keepItAlive');
        $keepAliveF->setAccessible(true);
        $keepAlive = $keepAliveF->invokeArgs($managerWorker, []);
        $this->assertTrue($keepAlive);
    }

    /**
     * Test with ttl
     *
     * @return void
     */
    public function testKeepAliveTTL()
    {
        $paramHash = self::$workerParamHash;
        $paramHash[] = '--ttl=2';

        $managerWorker = new Workflow\ManagerWorker();
        $managerWorkerReflection = new \ReflectionClass($managerWorker);
        $parseOtpF = $managerWorkerReflection->getMethod('parseOpt');
        $parseOtpF->setAccessible(true);
        $parsedOptHash = $parseOtpF->invokeArgs($managerWorker, [$paramHash]);
        $parseOpt = $managerWorkerReflection->getProperty('paramHash');
        $parseOpt->setAccessible(true);
        $parseOpt->setValue($managerWorker, $parsedOptHash);

        $initWorkerF = $managerWorkerReflection->getMethod('initWorker');
        $initWorkerF->setAccessible(true);
        $initWorkerF->invokeArgs($managerWorker, []);

        $keepAliveF = $managerWorkerReflection->getMethod('keepItAlive');
        $keepAliveF->setAccessible(true);
        $keepAlive = $keepAliveF->invokeArgs($managerWorker, []);
        $this->assertTrue($keepAlive);

        sleep(3);

        $keepAlive = $keepAliveF->invokeArgs($managerWorker, []);
        $this->assertFalse($keepAlive);
    }
}
