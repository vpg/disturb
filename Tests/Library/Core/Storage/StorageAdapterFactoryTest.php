<?php

namespace Tests\Library\Core\Storage;

use \Vpg\Disturb\Core\Storage;
use \Vpg\Disturb\Workflow;
use \Vpg\Disturb\Workflow\WorkflowConfigDtoFactory;


/**
 * Storage adapter factory test class
 *
 * @author  JEROME BOURGEAIS <jbourgeais@voyageprive.com>
 */
class StorageAdapterFactoryTest extends \Tests\DisturbUnitTestCase
{

    /**
     * Test adpater instanciation
     *
     * @return void
     */
    public function testElastic()
    {
        $workflowConfigDto = WorkflowConfigDtoFactory::get(realpath(__DIR__ .'/config/validWorkflowConfig.json'));
        $adapter = Storage\StorageAdapterFactory::get($workflowConfigDto, Storage\StorageAdapterFactory::USAGE_MONITORING);
        $this->assertInstanceOf('Vpg\\Disturb\\Core\\Storage\\ElasticsearchAdapter', $adapter);
    }
}
