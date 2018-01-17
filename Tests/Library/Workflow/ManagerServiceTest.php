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

    protected static $workflowSerieConfigDto;
    protected static $workflowParallelizedConfigDto;
    protected static $contextStorageService;
    protected static $serieWorkflowManagerService;
    protected static $parallelizedWorkflowManagerService;

    protected $workerHostname = 'worker-test-hostname';

    /**
     * Setup
     *
     * @return void
     */
    public static function setUpBeforeClass()
    {
        parent::setUpBeforeClass();
        self::$workflowSerieConfigDto = WorkflowConfigDtoFactory::get(realpath(__DIR__ . '/../../Config/serie.json'));
        self::$workflowParallelizedConfigDto = WorkflowConfigDtoFactory::get(
            realpath(__DIR__ . '/../../Config/parallelized.json')
        );
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
        self::$parallelizedWorkflowManagerService = new Workflow\ManagerService(self::$workflowParallelizedConfigDto);
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
     * Test init()
     *
     * @return void
     */
    public function testInit()
    {
        $wfId = $this->generateWfId();
        $this->expectException(Workflow\WorkflowException::class);
        self::$serieWorkflowManagerService->init($wfId, ['foo' => 'bar'], $this->workerHostname);
        self::$serieWorkflowManagerService->init($wfId, ['foo' => 'bar'], $this->workerHostname);
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
        self::$serieWorkflowManagerService->init($wfId, ['foo' => 'bar'], $this->workerHostname);
        self::$serieWorkflowManagerService->initNextStep($wfId);
        self::$serieWorkflowManagerService->registerStepJob($wfId, 'foo', 0);

        // Test reservation
        self::$serieWorkflowManagerService->reserveStepJob($wfId, 'foo', 0, $this->workerHostname, 'test-foo-0');
        $wfContextDto = self::$serieWorkflowManagerService->getContext($wfId);
        $this->assertEquals(
            'test-foo-0',
            $wfContextDto->getStep('foo')['jobList'][0]['reservedBy']
        );

        // Test reservation collision
        $this->expectException(Workflow\WorkflowJobReservationException::class);
        self::$serieWorkflowManagerService->reserveStepJob($wfId, 'foo', 0, $this->workerHostname, 'test-foo-0');

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
        self::$serieWorkflowManagerService->init($wfId, ['foo' => 'bar'], $this->workerHostname);
        self::$serieWorkflowManagerService->initNextStep($wfId);
        self::$serieWorkflowManagerService->registerStepJob($wfId, 'foo', 0);
        self::$serieWorkflowManagerService->registerStepJobStarted($wfId, 'foo', 0, $this->workerHostname);

        // Test finalization
        $resultHash = [
            'status' => Workflow\ManagerService::STATUS_SUCCESS,
            'finishedAt' => '2018-01-01 01:01:01',
            'data' => ['foo' => 'ok']
        ];
        self::$serieWorkflowManagerService->processStepJobResult($wfId, 'foo', 0, $resultHash);
        $wfContextDto = self::$serieWorkflowManagerService->getContext($wfId);
        $this->assertEquals(
            $resultHash['finishedAt'],
            $wfContextDto->getStep('foo')['jobList'][0]['finishedAt']
        );

        // Test finalization collision
        $this->expectException(Workflow\WorkflowJobFinalizationException::class);
        self::$serieWorkflowManagerService->processStepJobResult($wfId, 'foo', 0, $resultHash);

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
        self::$serieWorkflowManagerService->init($wfId, ['foo' => 'bar'], $this->workerHostname);
        $wfNextStepList = self::$serieWorkflowManagerService->getNextStepList($wfId);
        $this->assertEquals(
            [['name' => 'foo']],
            $wfNextStepList
        );
        // Process next step foo
        self::$serieWorkflowManagerService->initNextStep($wfId);
        self::$serieWorkflowManagerService->registerStepJob($wfId, $wfNextStepList[0]['name'], 0);
        self::$serieWorkflowManagerService->registerStepJobStarted($wfId, 'foo', 0, $this->workerHostname);
        $resultHash = [
            'status' => Workflow\ManagerService::STATUS_SUCCESS,
            'finishedAt' => '2018-01-01 01:01:01',
            'data' => ['foo' => 'ok']
        ];
        self::$serieWorkflowManagerService->processStepJobResult($wfId, 'foo', 0, $resultHash);
        // Process next step bar
        $wfNextStepList = self::$serieWorkflowManagerService->getNextStepList($wfId);
        $this->assertEquals(
            [['name' => 'bar']],
            $wfNextStepList
        );
        self::$serieWorkflowManagerService->initNextStep($wfId);
        self::$serieWorkflowManagerService->registerStepJob($wfId, $wfNextStepList[0]['name'], 0);
        self::$serieWorkflowManagerService->registerStepJobStarted($wfId, 'bar', 0, $this->workerHostname);
        $resultHash = [
            'status' => Workflow\ManagerService::STATUS_SUCCESS,
            'finishedAt' => '2018-01-01 01:02:01',
            'data' => ['bar' => 'ok']
        ];
        self::$serieWorkflowManagerService->processStepJobResult($wfId, 'bar', 0, $resultHash);
        $wfContextDto = self::$serieWorkflowManagerService->getContext($wfId);
        $this->assertEquals(
            $resultHash['data'],
            $wfContextDto->getStep('bar')['jobList'][0]['data']
        );
        $wfCurrentStepStatus = self::$serieWorkflowManagerService->getCurrentStepStatus($wfId);
        $this->assertEquals(
            Workflow\ManagerService::STATUS_SUCCESS,
            $wfCurrentStepStatus
        );

        // Finalize
        $wfHasNextStep = self::$serieWorkflowManagerService->hasNextStep($wfId);
        $this->assertFalse($wfHasNextStep);
        self::$serieWorkflowManagerService->finalize($wfId, Workflow\ManagerService::STATUS_SUCCESS);
        $wfStatus = self::$serieWorkflowManagerService->getStatus($wfId);
        $this->assertEquals(
            Workflow\ManagerService::STATUS_SUCCESS,
            $wfStatus
        );

        // clean db
        self::$contextStorageService->delete($wfId);
    }

    /**
     * Test a full WF execution
     *
     * @return void
     */
    public function testNoNextStep()
    {
        $wfId = $this->generateWfId();
        // init Work
        self::$serieWorkflowManagerService->init($wfId, ['foo' => 'bar'], $this->workerHostname);

        $wfNextStepList = self::$serieWorkflowManagerService->getNextStepList($wfId);
        self::$serieWorkflowManagerService->initNextStep($wfId);
        self::$serieWorkflowManagerService->registerStepJob($wfId, $wfNextStepList[0]['name'], 0);
        self::$serieWorkflowManagerService->registerStepJobStarted($wfId, 'foo', 0, $this->workerHostname);
        $resultHash = [
            'status' => Workflow\ManagerService::STATUS_SUCCESS,
            'finishedAt' => '2018-01-01 01:01:01',
            'data' => ['foo' => 'ok']
        ];
        self::$serieWorkflowManagerService->processStepJobResult($wfId, 'foo', 0, $resultHash);

        $wfNextStepList = self::$serieWorkflowManagerService->getNextStepList($wfId);
        self::$serieWorkflowManagerService->initNextStep($wfId);
        self::$serieWorkflowManagerService->registerStepJob($wfId, $wfNextStepList[0]['name'], 0);
        self::$serieWorkflowManagerService->registerStepJobStarted($wfId, 'bar', 0, $this->workerHostname);
        $resultHash = [
            'status' => Workflow\ManagerService::STATUS_SUCCESS,
            'finishedAt' => '2018-01-01 01:01:01',
            'data' => ['bar' => 'ok']
        ];
        self::$serieWorkflowManagerService->processStepJobResult($wfId, 'bar', 0, $resultHash);

        $this->expectException(Workflow\WorkflowException::class);
        $wfNextStepList = self::$serieWorkflowManagerService->getNextStepList($wfId);
    }

    /**
     * Test parallelized step fetch
     *
     * @return void
     */
    public function testGetNextParallelizedStep()
    {
        $wfId = $this->generateWfId();
        // init Work
        self::$parallelizedWorkflowManagerService->init($wfId, ['foo' => 'bar'], $this->workerHostname);
        $wfNextStepList = self::$parallelizedWorkflowManagerService->getNextStepList($wfId);
        $this->assertCount(2, $wfNextStepList);
        $this->assertEquals(
            [['name' => 'foo'],['name' => 'bar']],
            $wfNextStepList
        );
    }

    /**
     * Test parallelized step status agg
     *
     * @return void
     */
    public function testParallelizedStepStatus()
    {
        $wfId = $this->generateWfId();
        // init Work
        self::$parallelizedWorkflowManagerService->init($wfId, ['foo' => 'bar'], $this->workerHostname);
        $wfNextStepList = self::$parallelizedWorkflowManagerService->getNextStepList($wfId);
        // Process next step foo
        self::$parallelizedWorkflowManagerService->initNextStep($wfId);
        self::$parallelizedWorkflowManagerService->registerStepJob($wfId, $wfNextStepList[0]['name'], 0);
        self::$parallelizedWorkflowManagerService->registerStepJob($wfId, $wfNextStepList[1]['name'], 0);
        self::$parallelizedWorkflowManagerService->registerStepJobStarted(
            $wfId,
            $wfNextStepList[0]['name'],
            0,
            $this->workerHostname
        );
        self::$parallelizedWorkflowManagerService->registerStepJobStarted(
            $wfId,
            $wfNextStepList[1]['name'],
            0,
            $this->workerHostname
        );
        $resultHash = [
            'status' => Workflow\ManagerService::STATUS_SUCCESS,
            'finishedAt' => '2018-01-01 01:01:01',
            'data' => [$wfNextStepList[0]['name'] => 'ok']
        ];
        self::$parallelizedWorkflowManagerService->processStepJobResult(
            $wfId,
            $wfNextStepList[0]['name'],
            0,
            $resultHash
        );

        // one job is finished in success the other one is running
        $wfCurrentStepStatus = self::$parallelizedWorkflowManagerService->getCurrentStepStatus($wfId);
        $this->assertEquals(
            Workflow\ManagerService::STATUS_RUNNING,
            $wfCurrentStepStatus
        );

        $resultHash = [
            'status' => Workflow\ManagerService::STATUS_SUCCESS,
            'finishedAt' => '2018-01-01 01:01:01',
            'data' => [$wfNextStepList[1]['name'] => 'ok']
        ];
        self::$parallelizedWorkflowManagerService->processStepJobResult(
            $wfId,
            $wfNextStepList[1]['name'],
            0,
            $resultHash
        );

        // all jobs are in success
        $wfCurrentStepStatus = self::$parallelizedWorkflowManagerService->getCurrentStepStatus($wfId);
        $this->assertEquals(
            Workflow\ManagerService::STATUS_SUCCESS,
            $wfCurrentStepStatus
        );
        // clean db
        self::$contextStorageService->delete($wfId);
    }

    /**
     * Test parallelized step status agg
     *
     * @return void
     */
    public function testParallelizedStepStatusFailed()
    {
        $wfId = $this->generateWfId();
        // init Work
        self::$parallelizedWorkflowManagerService->init($wfId, ['foo' => 'bar'], $this->workerHostname);
        $wfNextStepList = self::$parallelizedWorkflowManagerService->getNextStepList($wfId);
        // Process next step foo
        self::$parallelizedWorkflowManagerService->initNextStep($wfId);
        self::$parallelizedWorkflowManagerService->registerStepJob($wfId, $wfNextStepList[0]['name'], 0);
        self::$parallelizedWorkflowManagerService->registerStepJob($wfId, $wfNextStepList[1]['name'], 0);
        self::$parallelizedWorkflowManagerService->registerStepJobStarted(
            $wfId,
            $wfNextStepList[0]['name'],
            0,
            $this->workerHostname
        );
        self::$parallelizedWorkflowManagerService->registerStepJobStarted(
            $wfId,
            $wfNextStepList[1]['name'],
            0,
            $this->workerHostname
        );
        $resultHash = [
            'status' => Workflow\ManagerService::STATUS_SUCCESS,
            'finishedAt' => '2018-01-01 01:01:01',
            'data' => [$wfNextStepList[0]['name'] => 'ok']
        ];
        self::$parallelizedWorkflowManagerService->processStepJobResult(
            $wfId,
            $wfNextStepList[0]['name'],
            0,
            $resultHash
        );

        // one job is finished in success the other one is running
        $wfCurrentStepStatus = self::$parallelizedWorkflowManagerService->getCurrentStepStatus($wfId);
        $this->assertEquals(
            Workflow\ManagerService::STATUS_RUNNING,
            $wfCurrentStepStatus
        );

        $resultHash = [
            'status' => Workflow\ManagerService::STATUS_FAILED,
            'finishedAt' => '2018-01-01 01:01:01',
            'data' => [$wfNextStepList[1]['name'] => 'ok']
        ];
        self::$parallelizedWorkflowManagerService->processStepJobResult(
            $wfId,
            $wfNextStepList[1]['name'],
            0,
            $resultHash
        );

        // all jobs are in success
        $wfCurrentStepStatus = self::$parallelizedWorkflowManagerService->getCurrentStepStatus($wfId);
        $this->assertEquals(
            Workflow\ManagerService::STATUS_FAILED,
            $wfCurrentStepStatus
        );
        // clean db
        self::$contextStorageService->delete($wfId);
    }

    /**
     * Test parallelized step fetch
     *
     * @return void
     */
    public function testNoJobToProccess()
    {
        $wfId = $this->generateWfId();
        // init Work
        self::$parallelizedWorkflowManagerService->init($wfId, ['foo' => 'bar'], $this->workerHostname);
        $wfNextStepList = self::$parallelizedWorkflowManagerService->getNextStepList($wfId);
        // Process next step foo
        self::$parallelizedWorkflowManagerService->initNextStep($wfId);
        self::$parallelizedWorkflowManagerService->registerStepJob($wfId, $wfNextStepList[0]['name'], 0);
        self::$parallelizedWorkflowManagerService->registerStepJob($wfId, $wfNextStepList[1]['name'], 0);
        self::$parallelizedWorkflowManagerService->registerStepJobStarted(
            $wfId,
            $wfNextStepList[0]['name'],
            0,
            $this->workerHostname
        );
        self::$parallelizedWorkflowManagerService->registerStepJobStarted(
            $wfId,
            $wfNextStepList[1]['name'],
            0,
            $this->workerHostname
        );
        $resultHash = [
            'status' => Workflow\ManagerService::STATUS_SUCCESS,
            'finishedAt' => '2018-01-01 01:01:01',
            'data' => [$wfNextStepList[0]['name'] => 'ok']
        ];
        $this->expectException(Workflow\WorkflowException::class);
        self::$parallelizedWorkflowManagerService->processStepJobResult(
            $wfId,
            $wfNextStepList[0]['name'],
            2,
            $resultHash
        );
    }
}
