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
     * Test init()
     *
     * @return void
     */
    public function tesInit()
    {
        $wfId = 'test' . microtime();
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
        $wfId = 'test' . microtime();
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
}
