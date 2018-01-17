<?php

namespace Tests\Library\Monitoring;

use \phalcon\Config;
use \Vpg\Disturb\Workflow;
use \Vpg\Disturb\Core;
use \Vpg\Disturb\Context\ContextStorageService;
use \Vpg\Disturb\Monitoring;


/**
 * Disturb client test class
 *
 * @author  JEROME BOURGEAIS <jbourgeais@voyageprive.com>
 */
class ServiceTest extends \Tests\DisturbUnitTestCase
{

    protected static $workflowConfigDto;
    protected static $monitoringService;

    /**
     * Setup
     *
     * @return void
     */
    public function setUp()
    {
        parent::setUp();
        self::$workflowConfigDto = Workflow\WorkflowConfigDtoFactory::get(realpath(__DIR__ . '/config.json'));
        self::$monitoringService = new Monitoring\Service(self::$workflowConfigDto);
    }


    /**
     * returns a new worker id
     *
     * @return string a brand new worker id
     */
    private function generateWorkerId(): string
    {
        return str_replace(' ', '', 'worker-test-hostname-' . microtime());
    }

    /**
     * Tests worker heart beat
     *
     * @return void
     */
    public function testHeartbeat()
    {
        $workerId = $this->generateWorkerId();
        self::$monitoringService->logWorkerBeat($workerId);
        $workerHash = self::$monitoringService->getWorkerInfo($workerId);
        $beat1 = date($workerHash['heartBeatAt']);
        $this->assertArrayHasKey('heartBeatAt', $workerHash);
        sleep(1);
        self::$monitoringService->logWorkerBeat($workerId);
        $workerHash = self::$monitoringService->getWorkerInfo($workerId);
        $beat2 = date($workerHash['heartBeatAt']);
        $this->assertGreaterThan($beat1, $beat2);
        self::$monitoringService->deleteWorkerInfo($workerId);
    }

    /**
     * Tests worker started
     *
     * @return void
     */
    public function testStarted()
    {
        $workerId = $this->generateWorkerId();
        self::$monitoringService->logWorkerStarted($workerId, $pid = 1);
        $workerHash = self::$monitoringService->getWorkerInfo($workerId);
        $this->assertEquals(Core\AbstractWorker::STATUS_STARTED, $workerHash['status']);
        self::$monitoringService->deleteWorkerInfo($workerId);
    }

    /**
     * Tests worker exited
     *
     * @return void
     */
    public function testExited()
    {
        $workerId = $this->generateWorkerId();
        self::$monitoringService->logWorkerExited($workerId, $pid = 1);
        $workerHash = self::$monitoringService->getWorkerInfo($workerId);
        $this->assertEquals(Core\AbstractWorker::STATUS_EXITED, $workerHash['status']);
        self::$monitoringService->deleteWorkerInfo($workerId);
    }

}
