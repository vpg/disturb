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

    protected static $contextStorageService;
    protected static $workflowManagerService;
    protected static $workflowConfigDto;

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
        self::$contextStorageService = new ContextStorageService(self::$workflowConfigDto);
        self::$workflowManagerService = new Workflow\ManagerService(self::$workflowConfigDto);
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
        $disturbClient = new Client\Disturb(self::$workflowConfigDto);
        $wfId = $this->generateWfId();
        self::$workflowManagerService->init($wfId, ['foo' => 'bar'], $this->workerHostname);
        $wfHash = $disturbClient->getWorkFlow($wfId);

        $this->assertNull($wfHash->validate());
        $this->addToAssertionCount(1);

        // clean db
        self::$contextStorageService->delete($wfId);
    }
}
