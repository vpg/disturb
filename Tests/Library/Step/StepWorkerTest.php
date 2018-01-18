<?php

namespace Tests\Library\Step;

use \phalcon\Config;
use \Vpg\Disturb\Core\Worker;
use \Vpg\Disturb\Workflow;
use \Vpg\Disturb\Step;
use \Vpg\Disturb\Message\MessageDto;
use \Vpg\Disturb\Context\ContextStorageService;
use \Vpg\Disturb\Workflow\WorkflowConfigDtoFactory;


/**
 * Step Worker test class
 *
 * @author JEROME BOURGEAIS <jbourgeais@voyageprive.com>
 */
class StepWorkerTest extends \Tests\DisturbUnitTestCase
{

    protected static $workflowSerieConfigDto;
    protected static $workerParamHash;
    protected static $contextStorageService;
    protected $managerWorker;
    protected $stepWorker;

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
     * Test init()
     *
     * @return void
     */
    public function testBadInitClientParams()
    {
        $configFilepath = realpath(__DIR__ . '/../../Config/serieWrongClientClass.json');
        $workerParamHash = [
        ];
        $stepWorker = new Step\StepWorker();
        $stepWorkerReflection = new \ReflectionClass($stepWorker);
        $parseOtpF = $stepWorkerReflection->getMethod('parseOpt');
        $parseOtpF->setAccessible(true);
        $this->expectException(Worker\WorkerException::class);
        $parsedOptHash = $parseOtpF->invokeArgs($stepWorker, [$workerParamHash]);
    }

    /**
     * Test start workflow
     *
     * @return void
     */
    public function testBadWorkerClientClass()
    {
        $configFilepath = realpath(__DIR__ . '/../../Config/serieWrongClientClass.json');
        $workerParamHash = [
            "--workflow=$configFilepath",
            "--step=badfoo"
        ];
        $stepWorker = new Step\StepWorker();
        $stepWorkerReflection = new \ReflectionClass($stepWorker);
        $parseOtpF = $stepWorkerReflection->getMethod('parseOpt');
        $parseOtpF->setAccessible(true);
        $parsedOptHash = $parseOtpF->invokeArgs($stepWorker, [$workerParamHash]);

        $paramHashF = $stepWorkerReflection->getProperty('paramHash');
        $paramHashF->setAccessible(true);
        $paramHashF->setValue($stepWorker, $parsedOptHash);

        $initWorkerF = $stepWorkerReflection->getMethod('initWorker');
        $initWorkerF->setAccessible(true);
        $this->expectException(Step\StepException::class);
        $initWorkerF->invokeArgs($stepWorker, [self::$workerParamHash]);
    }

    /**
     * Test start workflow
     *
     * @return void
     */
    public function testStartWorkflow()
    {
        // start workflow
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

        // Start step
        $configFilepath = realpath(__DIR__ . '/../../Config/serie.json');
        $workerParamHash = [
            "--workflow=$configFilepath",
            "--step=foo"
        ];
        $stepWorker = new Step\StepWorker();
        $stepWorkerReflection = new \ReflectionClass($stepWorker);
        $parseOtpF = $stepWorkerReflection->getMethod('parseOpt');
        $parseOtpF->setAccessible(true);
        $parsedOptHash = $parseOtpF->invokeArgs($stepWorker, [$workerParamHash]);

        $paramHashF = $stepWorkerReflection->getProperty('paramHash');
        $paramHashF->setAccessible(true);
        $paramHashF->setValue($stepWorker, $parsedOptHash);

        $initWorkerF = $stepWorkerReflection->getMethod('initWorker');
        $initWorkerF->setAccessible(true);
        $initWorkerF->invokeArgs($stepWorker, [self::$workerParamHash]);

        $startWFMsg = [
            'id' => $wfId,
            'type' => 'STEP-CTRL',
            'stepCode' => 'foo',
            'jobId' => '1',
            'action' => 'start',
            'payload' => ['foo' => 'bar0']
        ];
        $msgDto = new MessageDto($startWFMsg);

        $fooStepMock = $this->getMockBuilder('\Vpg\Disturb\Test\FooStep')
            ->setMethods(['beforeExecute', 'afterExecute'])
            ->getMock();

        $stepService = $stepWorkerReflection->getProperty('service');
        $stepService->setAccessible(true);
        $stepService->setValue($stepWorker, $fooStepMock);

        $fooStepMock->expects($this->once())
            ->method('beforeExecute');
        $fooStepMock->expects($this->once())
            ->method('afterExecute');
        $processMessageF = $stepWorkerReflection->getMethod('processMessage');
        $processMessageF->setAccessible(true);
        $processMessageF->invokeArgs($stepWorker, [$msgDto]);

        $wfDto = self::$contextStorageService->get($wfId);
    }
}
