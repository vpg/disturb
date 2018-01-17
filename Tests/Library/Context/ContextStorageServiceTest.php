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

    protected static $contextStorageService;
    protected static $workflowManagerService;

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
    public static function setUpBeforeClass()
    {
        parent::setUpBeforeClass();
        $workflowConfigDto = WorkflowConfigDtoFactory::get(realpath(__DIR__ . '/../../Config/serie.json'));
        self::$workflowManagerService = new Workflow\ManagerService($workflowConfigDto);
        self::$contextStorageService = new ContextStorageService($workflowConfigDto);
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
        self::$workflowManagerService->init($wfId, $this->validWFHash, $this->workerHostname);
        self::$contextStorageService->setWorkflowStatus($wfId, Workflow\ManagerService::STATUS_SUCCESS, 'test');
        $wfStatus = self::$workflowManagerService->getStatus($wfId);
        $this->assertEquals(Workflow\ManagerService::STATUS_SUCCESS, $wfStatus);
        $wfDto = self::$contextStorageService->get($wfId);
        $wfInfo = $wfDto->getWorkflowInfo($wfId);
        $this->assertEquals('test', $wfInfo);
        self::$contextStorageService->delete($wfId);
    }
}
