<?php

namespace Tests\Library\Client;

use \phalcon\Config;
use \Vpg\Disturb\Workflow;
use \Vpg\Disturb\Client;
use \Vpg\Disturb\Context\ContextStorageService;
use \Vpg\Disturb\Workflow\WorkflowConfigDtoFactory;


/**
 * Disturb client test class
 *
 * @author  JEROME BOURGEAIS <jbourgeais@voyageprive.com>
 */
class DisturbTest extends \Tests\DisturbUnitTestCase
{

    protected $workflowConfigDto;
    protected $contextStorageService;
    protected $workflowManagerService;
    protected $disturbClient;

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
        $this->disturbClient = new Client\Disturb();
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
     * Test getWf()
     *
     * @return void
     */
    public function testGetWorkflow()
    {
        $wfId = $this->generateWfId();
        $this->workflowManagerService->init($wfId, ['foo' => 'bar'], $this->workerHostname);
        $wfHash = $this->disturbClient->getWorkFlow($wfId);

        $this->assertNull($wfHash->validate());
        $this->addToAssertionCount(1);

        // clean db
        $this->contextStorageService->delete($wfId);
    }

}
