<?php

namespace Tests\Library\Workflow;

use \phalcon\Config;
use \Vpg\Disturb\Core\Worker;
use \Vpg\Disturb\Workflow;
use \Vpg\Disturb\Message\MessageDto;
use \Vpg\Disturb\Context\ContextStorageService;
use \Vpg\Disturb\Workflow\WorkflowConfigDtoFactory;
use Tests\Helper\Workflow\ManagerHelper;


/**
 * Manager Worker test class
 *
 * @author JEROME BOURGEAIS <jbourgeais@voyageprive.com>
 */
class ManagerWorkerTest extends \Tests\DisturbUnitTestCase
{

    protected static $workflowSerieConfigDto;
    protected static $workflowWithoutJobConfigDto;
    protected static $workerParamHash;
    protected static $workerWithoutJobParamHash;
    protected static $contextStorageService;
    protected static $serieWorkflowManagerService;
    protected static $withoutJobWorkflowManagerService;
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
        $configWithoutJobFilepath = realpath(__DIR__ . '/../../Config/withoutJob.json');
        self::$workflowWithoutJobConfigDto = WorkflowConfigDtoFactory::get($configWithoutJobFilepath);
        self::$contextStorageService = new ContextStorageService(self::$workflowSerieConfigDto);
        self::$workerParamHash = [
           "--workflow=$configFilepath"
        ];
        self::$workerWithoutJobParamHash = [
            "--workflow=$configWithoutJobFilepath"
        ];
    }

    /**
     * Setup
     *
     * @return void
     */
    public function setUp()
    {
        parent::setUp();
        self::$serieWorkflowManagerService = new Workflow\ManagerService(self::$workflowSerieConfigDto);
        self::$withoutJobWorkflowManagerService = new Workflow\ManagerService(self::$workflowWithoutJobConfigDto);
        self::$contextStorageService = new ContextStorageService(self::$workflowSerieConfigDto);
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
        $managerHelper = new ManagerHelper(self::$workerParamHash);
        $wfId = $this->generateWfId();
        $managerHelper->processMessage('{"id":"' . $wfId . '", "type" : "WF-CONTROL", "action":"start", "payload": {"foo":"bar"}}');

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
        $managerHelper = new ManagerHelper(self::$workerParamHash);
        $wfId = $this->generateWfId();
        $processed = $managerHelper->processMessage('{"id":"' . $wfId . '", "type" : "WF-CONTROL", "action":"start", "payload": {"foo":"bar"}}');
        $this->assertTrue($processed);

        $processed = $managerHelper->processMessage('{"id":"' . $wfId . '", "type" : "WF-CONTROL", "action":"start", "payload": {"foo":"bar"}}');
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
        $this->expectException(Workflow\WorkflowException::class);
        $managerHelper = new ManagerHelper($workerParamHash);
    }

    /**
     * Test ttl
     *
     * @return void
     */
    public function testKeepAliveNoTTL()
    {
        $managerHelper = new ManagerHelper(self::$workerParamHash);
        $keepAlive = $managerHelper->keepItAlive();
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

        $managerHelper = new ManagerHelper($paramHash);
        $keepAlive = $managerHelper->keepItAlive();
        $this->assertTrue($keepAlive);

        sleep(3);

        $keepAlive = $managerHelper->keepItAlive();
        $this->assertFalse($keepAlive);
    }

    /**
     * Test a full WF execution with a step without job
     * { "name" : "foo" },
     * { "name" : "noJob" },
     * { "name" : "bar" },
     * [
     *   { "name" : "boo" },
     *   { "name" : "noJob" }
     * ],
     * [
     *   { "name" : "noJob" },
     *   { "name" : "noJob" }
     * ]
     *
     * @return void
     */
    public function testNoStepJobToRun()
    {
        $managerHelper = new ManagerHelper(self::$workerWithoutJobParamHash);

        //simulate workflow start message
        $wfId = $this->generateWfId();
        $managerHelper->processMessage('{"id":"' . $wfId . '", "type" : "WF-CONTROL", "action":"start", "payload": {"foo":"bar"}}');

        $wfDto = self::$contextStorageService->get($wfId);

        //test if workflow is properly started
        $wfStatus = $wfDto->getWorkflowStatus();
        $this->assertEquals(
            Workflow\ManagerService::STATUS_STARTED,
            $wfStatus
        );

        // test if foo is started
        $fooStepHash = $wfDto->getStep('foo');
        $this->assertEquals(
            Workflow\ManagerService::STATUS_NO_STARTED,
            $fooStepHash['jobList'][0]['status']
        );

        //simulate foo successful execution
        //process message foo -> run next step on "noJob step"
        $managerHelper->processMessage('{"id":"' . $wfId . '", "type":"STEP-ACK","stepCode":"foo","jobId":"0","result":{"status":"SUCCESS","data":[],"finishedAt":"2018-01-30 16:39:26"}}');

        //get workflow in order to verify if "noJob" step has been skipped
        $wfDto = self::$contextStorageService->get($wfId);
        $noJobStepHash = $wfDto->getStep('noJob');
        $this->assertEmpty(
            $noJobStepHash['jobList']
        );
        $this->assertNotEmpty(
            $noJobStepHash['skippedAt']
        );

        //get workflow in order to verify if "bar" is started
        $wfDto = self::$contextStorageService->get($wfId);
        $barStepHash = $wfDto->getStep('bar');
        $this->assertNotEmpty(
            $barStepHash['jobList']
        );

        //simulate bar successful execution
        $managerHelper->processMessage('{"id":"' . $wfId . '", "type":"STEP-ACK","stepCode":"bar","jobId":"0","result":{"status":"SUCCESS","data":[],"finishedAt":"2018-01-30 16:39:26"}}');
        $managerHelper->processMessage('{"id":"' . $wfId . '", "type":"STEP-ACK","stepCode":"bar","jobId":"1","result":{"status":"SUCCESS","data":[],"finishedAt":"2018-01-30 16:39:26"}}');

        // test if boo is started
        $wfDto = self::$contextStorageService->get($wfId);
        $booStepHash = $wfDto->getStep('boo');
        $this->assertEquals(
            Workflow\ManagerService::STATUS_NO_STARTED,
            $booStepHash['jobList'][0]['status']
        );

        //get workflow in order to verify if "noJobParallelized" step has been skipped
        $wfDto = self::$contextStorageService->get($wfId);
        $noJobParallelizedStepHash = $wfDto->getStep('noJobParallelized');
        $this->assertEmpty(
            $noJobParallelizedStepHash['jobList']
        );
        $this->assertNotEmpty(
            $noJobParallelizedStepHash['skippedAt']
        );

        //simulate foo successful execution
        //process message foo -> run next step on "noJob step"
        $managerHelper->processMessage('{"id":"' . $wfId . '", "type":"STEP-ACK","stepCode":"boo","jobId":"0","result":{"status":"SUCCESS","data":[],"finishedAt":"2018-01-30 16:39:26"}}');

        //get workflow in order to verify if "noJobParallelizedBis" step has been skipped
        $wfDto = self::$contextStorageService->get($wfId);
        $noJobParallelizedStepHash = $wfDto->getStep('noJobParallelizedBis');
        $this->assertEmpty(
            $noJobParallelizedStepHash['jobList']
        );
        $this->assertNotEmpty(
            $noJobParallelizedStepHash['skippedAt']
        );

        //get workflow in order to verify if "noJobParallelizedTris" step has been skipped
        $wfDto = self::$contextStorageService->get($wfId);
        $noJobParallelizedStepHash = $wfDto->getStep('noJobParallelizedTris');
        $this->assertEmpty(
            $noJobParallelizedStepHash['jobList']
        );
        $this->assertNotEmpty(
            $noJobParallelizedStepHash['skippedAt']
        );

        //test if workflow is properly ended
        $wfStatus = $wfDto->getWorkflowStatus();
        $this->assertEquals(
            Workflow\ManagerService::STATUS_SUCCESS,
            $wfStatus
        );

        // clean db
        self::$contextStorageService->delete($wfId);
    }
}
