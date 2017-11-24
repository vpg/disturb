<?php

namespace Tests\Library\Core\Storage;

use Vpg\Disturb\Core\Storage;
use Vpg\Disturb\Workflow;


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
        $config = new Workflow\WorkflowConfigDto(__DIR__ . '/config/validWorkflowConfig.json');
        $adapter = Storage\StorageAdapterFactory::get($config, ['index' => 'disturb_monitoring', 'type' => 'worker']);
        $this->assertInstanceOf('Vpg\\Disturb\\Core\\Storage\\ElasticsearchAdapter', $adapter);
    }
}
