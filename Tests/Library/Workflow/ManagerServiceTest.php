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

    protected $workflowConfigDto;
    protected $contextStorageService;
    protected $workflowManagerService;

    protected $workerHostname = 'worker-test-hostname';

    /**
     * Setup
     *
     * @return void
     */
    public function setUp()
    {
        parent::setUp();

        $this->workflowConfigDto = WorkflowConfigDtoFactory::get(realpath(__DIR__ . '/config.json'));
        $this->contextStorageService = new ContextStorageService($this->workflowConfigDto);
        $this->workflowManagerService = new Workflow\ManagerService($this->workflowConfigDto);
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
        $this->workflowManagerService->init($wfId, ['foo' => 'bar'], $this->workerHostname);
        $this->workflowManagerService->init($wfId, ['foo' => 'bar'], $this->workerHostname);
        // clean db
        $this->contextStorageService->delete($wfId);
    }

    /**
     * Test reserveStepJob()
     *
     * @return void
     */
    public function testReserveStepJob()
    {
        $wfId = $this->generateWfId();
        $this->workflowManagerService->init($wfId, ['foo' => 'bar'], $this->workerHostname);
        $this->workflowManagerService->initNextStep($wfId);
        $this->workflowManagerService->registerStepJob($wfId, 'foo', 0);

        // Test reservation
        $this->workflowManagerService->reserveStepJob($wfId, 'foo', 0, $this->workerHostname, 'test-foo-0');
        $wfContextDto = $this->workflowManagerService->getContext($wfId);
        $this->assertEquals(
            'test-foo-0',
            $wfContextDto->getStep('foo')['jobList'][0]['reservedBy']
        );

        // Test reservation collision
        $this->expectException(Workflow\WorkflowJobReservationException::class);
        $this->workflowManagerService->reserveStepJob($wfId, 'foo', 0, $this->workerHostname, 'test-foo-0');

        // clean db
        $this->contextStorageService->delete($wfId);
    }

    /**
     * Test processStepJobResult()
     *
     * @return void
     */
    public function testProcessStepJobResult()
    {
        $wfId = $this->generateWfId();
        $this->workflowManagerService->init($wfId, ['foo' => 'bar'], $this->workerHostname);
        $this->workflowManagerService->initNextStep($wfId);
        $this->workflowManagerService->registerStepJob($wfId, 'foo', 0);
        $this->workflowManagerService->registerStepJobStarted($wfId, 'foo', 0, $this->workerHostname);

        // Test finalization
        $resultHash = [
            'status' => Workflow\ManagerService::STATUS_SUCCESS,
            'finishedAt' => '2018-01-01 01:01:01',
            'data' => ['foo' => 'ok']
        ];
        $this->workflowManagerService->processStepJobResult($wfId, 'foo', 0, $resultHash);
        $wfContextDto = $this->workflowManagerService->getContext($wfId);
        $this->assertEquals(
            $resultHash['finishedAt'],
            $wfContextDto->getStep('foo')['jobList'][0]['finishedAt']
        );

        // Test finalization collision
        $this->expectException(Workflow\WorkflowJobFinalizationException::class);
        $this->workflowManagerService->processStepJobResult($wfId, 'foo', 0, $resultHash);

        // clean db
        $this->contextStorageService->delete($wfId);
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
        $this->workflowManagerService->init($wfId, ['foo' => 'bar'], $this->workerHostname);
        $wfNextStepList = $this->workflowManagerService->getNextStepList($wfId);
        $this->assertEquals(
            [['name' => 'foo']],
            $wfNextStepList
        );
        // Process next step foo
        $this->workflowManagerService->initNextStep($wfId);
        $this->workflowManagerService->registerStepJob($wfId, $wfNextStepList[0]['name'], 0);
        $this->workflowManagerService->registerStepJobStarted($wfId, 'foo', 0, $this->workerHostname);
        $resultHash = [
            'status' => Workflow\ManagerService::STATUS_SUCCESS,
            'finishedAt' => '2018-01-01 01:01:01',
            'data' => ['foo' => 'ok']
        ];
        $this->workflowManagerService->processStepJobResult($wfId, 'foo', 0, $resultHash);
        // Process next step bar
        $wfNextStepList = $this->workflowManagerService->getNextStepList($wfId);
        $this->assertEquals(
            [['name' => 'bar']],
            $wfNextStepList
        );
        $this->workflowManagerService->initNextStep($wfId);
        $this->workflowManagerService->registerStepJob($wfId, $wfNextStepList[0]['name'], 0);
        $this->workflowManagerService->registerStepJobStarted($wfId, 'bar', 0, $this->workerHostname);
        $resultHash = [
            'status' => Workflow\ManagerService::STATUS_SUCCESS,
            'finishedAt' => '2018-01-01 01:02:01',
            'data' => ['bar' => 'ok']
        ];
        $this->workflowManagerService->processStepJobResult($wfId, 'bar', 0, $resultHash);
        $wfContextDto = $this->workflowManagerService->getContext($wfId);
        $this->assertEquals(
            $resultHash['data'],
            $wfContextDto->getStep('bar')['jobList'][0]['result']
        );
        $wfCurrentStepStatus = $this->workflowManagerService->getCurrentStepStatus($wfId);
        $this->assertEquals(
            Workflow\ManagerService::STATUS_SUCCESS,
            $wfCurrentStepStatus
        );

        // Finalize
        $wfHasNextStep = $this->workflowManagerService->hasNextStep($wfId);
        $this->assertFalse($wfHasNextStep);
        $this->workflowManagerService->finalize($wfId, Workflow\ManagerService::STATUS_SUCCESS);
        $wfStatus = $this->workflowManagerService->getStatus($wfId);
        $this->assertEquals(
            Workflow\ManagerService::STATUS_SUCCESS,
            $wfStatus
        );

        // clean db
        $this->contextStorageService->delete($wfId);
    }
}
