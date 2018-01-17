<?php

namespace Tests\Library\Context;

use \phalcon\Config;
use \Vpg\Disturb\Workflow;
use \Vpg\Disturb\Context\ContextStorageService;
use \Vpg\Disturb\Workflow\WorkflowConfigDtoFactory;


/**
 * Workflow Manager Service test class
 *
 * @author  JEROME BOURGEAIS <jbourgeais@voyageprive.com>
 */
class ManagerServiceTest extends \Tests\DisturbUnitTestCase
{

    protected static $workflowConfigDto;
    protected static $contextStorageService;
    protected static $workflowManagerService;

    protected $workerHostname = 'worker-test-hostname';

    /**
     * Setup
     *
     * @return void
     */
    public static function setUpBeforeClass()
    {
        parent::setUpBeforeClass();

        self::$workflowConfigDto = WorkflowConfigDtoFactory::get(realpath(__DIR__ . '/config.json'));
        echo '-';
        self::$workflowManagerService = new Workflow\ManagerService(self::$workflowConfigDto);
        echo '-';
        self::$contextStorageService = new ContextStorageService(self::$workflowConfigDto);
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
    public function testInit()
    {
        $wfId = $this->generateWfId();
        $this->expectException(Workflow\WorkflowException::class);
        self::$workflowManagerService->init($wfId, ['foo' => 'bar'], $this->workerHostname);
        self::$workflowManagerService->init($wfId, ['foo' => 'bar'], $this->workerHostname);
        // clean db
        self::$contextStorageService->delete($wfId);
    }

    /**
     * Test reserveStepJob()
     *
     * @return void
     */
    public function testReserveStepJob()
    {
        $wfId = $this->generateWfId();
        self::$workflowManagerService->init($wfId, ['foo' => 'bar'], $this->workerHostname);
        self::$workflowManagerService->initNextStep($wfId);
        self::$workflowManagerService->registerStepJob($wfId, 'foo', 0);

        // Test reservation
        self::$workflowManagerService->reserveStepJob($wfId, 'foo', 0, $this->workerHostname, 'test-foo-0');
        $wfContextDto = self::$workflowManagerService->getContext($wfId);
        $this->assertEquals(
            'test-foo-0',
            $wfContextDto->getStep('foo')['jobList'][0]['reservedBy']
        );

        // Test reservation collision
        $this->expectException(Workflow\WorkflowJobReservationException::class);
        self::$workflowManagerService->reserveStepJob($wfId, 'foo', 0, $this->workerHostname, 'test-foo-0');

        // clean db
        self::$contextStorageService->delete($wfId);
    }

    /**
     * Test processStepJobResult()
     *
     * @return void
     */
    public function testProcessStepJobResult()
    {
        $wfId = $this->generateWfId();
        self::$workflowManagerService->init($wfId, ['foo' => 'bar'], $this->workerHostname);
        self::$workflowManagerService->initNextStep($wfId);
        self::$workflowManagerService->registerStepJob($wfId, 'foo', 0);
        self::$workflowManagerService->registerStepJobStarted($wfId, 'foo', 0, $this->workerHostname);

        // Test finalization
        $resultHash = [
            'status' => Workflow\ManagerService::STATUS_SUCCESS,
            'finishedAt' => '2018-01-01 01:01:01',
            'data' => ['foo' => 'ok']
        ];
        self::$workflowManagerService->processStepJobResult($wfId, 'foo', 0, $resultHash);
        $wfContextDto = self::$workflowManagerService->getContext($wfId);
        $this->assertEquals(
            $resultHash['finishedAt'],
            $wfContextDto->getStep('foo')['jobList'][0]['finishedAt']
        );

        // Test finalization collision
        $this->expectException(Workflow\WorkflowJobFinalizationException::class);
        self::$workflowManagerService->processStepJobResult($wfId, 'foo', 0, $resultHash);

        // clean db
        self::$contextStorageService->delete($wfId);
    }

    /**
     * Test a full WF execution
     *
     * @return void
     */
    public function testFullWorkflowExec()
    {
        $wfId = $this->generateWfId();
        // init Work
        self::$workflowManagerService->init($wfId, ['foo' => 'bar'], $this->workerHostname);
        $wfNextStepList = self::$workflowManagerService->getNextStepList($wfId);
        $this->assertEquals(
            [['name' => 'foo']],
            $wfNextStepList
        );
        // Process next step foo
        self::$workflowManagerService->initNextStep($wfId);
        self::$workflowManagerService->registerStepJob($wfId, $wfNextStepList[0]['name'], 0);
        self::$workflowManagerService->registerStepJobStarted($wfId, 'foo', 0, $this->workerHostname);
        $resultHash = [
            'status' => Workflow\ManagerService::STATUS_SUCCESS,
            'finishedAt' => '2018-01-01 01:01:01',
            'data' => ['foo' => 'ok']
        ];
        self::$workflowManagerService->processStepJobResult($wfId, 'foo', 0, $resultHash);
        // Process next step bar
        $wfNextStepList = self::$workflowManagerService->getNextStepList($wfId);
        $this->assertEquals(
            [['name' => 'bar']],
            $wfNextStepList
        );
        self::$workflowManagerService->initNextStep($wfId);
        self::$workflowManagerService->registerStepJob($wfId, $wfNextStepList[0]['name'], 0);
        self::$workflowManagerService->registerStepJobStarted($wfId, 'bar', 0, $this->workerHostname);
        $resultHash = [
            'status' => Workflow\ManagerService::STATUS_SUCCESS,
            'finishedAt' => '2018-01-01 01:02:01',
            'data' => ['bar' => 'ok']
        ];
        self::$workflowManagerService->processStepJobResult($wfId, 'bar', 0, $resultHash);
        $wfContextDto = self::$workflowManagerService->getContext($wfId);
        $this->assertEquals(
            $resultHash['data'],
            $wfContextDto->getStep('bar')['jobList'][0]['data']
        );
        $wfCurrentStepStatus = self::$workflowManagerService->getCurrentStepStatus($wfId);
        $this->assertEquals(
            Workflow\ManagerService::STATUS_SUCCESS,
            $wfCurrentStepStatus
        );

        // Finalize
        $wfHasNextStep = self::$workflowManagerService->hasNextStep($wfId);
        $this->assertFalse($wfHasNextStep);
        self::$workflowManagerService->finalize($wfId, Workflow\ManagerService::STATUS_SUCCESS);
        $wfStatus = self::$workflowManagerService->getStatus($wfId);
        $this->assertEquals(
            Workflow\ManagerService::STATUS_SUCCESS,
            $wfStatus
        );

        // clean db
        self::$contextStorageService->delete($wfId);
    }
}
