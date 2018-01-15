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
class ContextStorageServiceTest extends \Tests\DisturbUnitTestCase
{

    protected $workflowConfigDto;
    protected $contextStorageService;

    protected $workerHostname = 'worker-test-hostname';

    protected $validWFHash = [
        'steps' => [
            [
                'name' => 'foo'
            ]
        ],
        'initialPayload' => [
            'foo' => 'bar',
        ],
        'status' => 'STARTED',
    ];

    /**
     * Setup
     *
     * @return void
     */
    public function setUp()
    {
        parent::setUp();

        $this->workflowConfigDto = WorkflowConfigDtoFactory::get(realpath(__DIR__ . '/../../Config/serie.json'));
        $this->workflowManagerService = new Workflow\ManagerService($this->workflowConfigDto);
        $this->contextStorageService = new ContextStorageService($this->workflowConfigDto);
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
     * Test set status()
     *
     * @return void
     */
    public function testSetStatus()
    {
        $wfId = $this->generateWfId();
        $this->workflowManagerService->init($wfId, $this->validWFHash, $this->workerHostname);
        $this->contextStorageService->setWorkflowStatus($wfId, Workflow\ManagerService::STATUS_SUCCESS, 'test');
        $wfStatus = $this->workflowManagerService->getStatus($wfId);
        $this->assertEquals(Workflow\ManagerService::STATUS_SUCCESS, $wfStatus);
        $wfDto = $this->contextStorageService->get($wfId);
        $wfInfo = $wfDto->getWorkflowInfo($wfId);
        $this->assertEquals('test', $wfInfo);
        $this->contextStorageService->delete($wfId);
    }
}
