<?php

namespace Tests\Library\Core\Storage;

use \Vpg\Disturb\Core\Storage;
use \Vpg\Disturb\Workflow;
use \Vpg\Disturb\Workflow\WorkflowConfigDtoFactory;
use \Vpg\Disturb\Workflow\InvalidWorkflowConfigException;


/**
 * Storage adapter factory test class
 *
 * @author  JEROME BOURGEAIS <jbourgeais@voyageprive.com>
 */
class StorageAdapterFactoryTest extends \Tests\DisturbUnitTestCase
{

    /**
     * Test els adpater : valid instantiation
     *
     * @return void
     */
    public function testValidElasticAdapter()
    {
        $workflowConfigDto = WorkflowConfigDtoFactory::get(realpath(__DIR__ . '/Config/validWorkflowConfig.json'));
        $adapter = Storage\StorageAdapterFactory::get(
            $workflowConfigDto,
            Storage\StorageAdapterFactory::USAGE_MONITORING
        );
        $this->assertInstanceOf('Vpg\\Disturb\\Core\\Storage\\ElasticsearchAdapter', $adapter);
    }

    /**
     * Test els adpater : invalid instantiation
     *
     * @return void
     */
    public function testMissingElasticAdapter()
    {
        $workflowConfigDto = WorkflowConfigDtoFactory::get(
            realpath(__DIR__ . '/../../../Config/InvalidWorkflowConfig-MissingStorageAdapter.json')
        );
        $this->expectException(Storage\StorageException::class);
        $adapter = Storage\StorageAdapterFactory::get(
            $workflowConfigDto,
            Storage\StorageAdapterFactory::USAGE_MONITORING
        );
    }

    /**
     * Test els adpater : invalid instantiation
     *
     * @return void
     */
    public function testInvalidElasticAdapter()
    {

        $workflowConfigDto = WorkflowConfigDtoFactory::get(
            realpath(__DIR__ . '/../../../Config/InvalidWorkflowConfig-WrongStorageAdapter.json')
        );
        $this->expectException(Storage\StorageException::class);
        $adapter = Storage\StorageAdapterFactory::get(
            $workflowConfigDto,
            Storage\StorageAdapterFactory::USAGE_MONITORING
        );
    }

    /**
     * Test els adpater : invalid instantiation
     *
     * @return void
     */
    public function testMissingElasticAdapterConfig()
    {
        $this->expectException(InvalidWorkflowConfigException::class);
        $workflowConfigDto = WorkflowConfigDtoFactory::get(
            realpath(__DIR__ . '/../../../Config/InvalidWorkflowConfig-WrongStorageAdapterConfig.json')
        );
        $adapter = Storage\StorageAdapterFactory::get(
            $workflowConfigDto,
            Storage\StorageAdapterFactory::USAGE_MONITORING
        );
    }

    /**
     * Test els adpater : invalid instantiation
     *
     * @return void
     */
    public function testInvalidElasticAdapterConfig()
    {
        $workflowConfigDto = WorkflowConfigDtoFactory::get(realpath(__DIR__ . '/Config/validWorkflowConfig.json'));
        $reflection = new \ReflectionClass($workflowConfigDto);
        $reflection_property = $reflection->getProperty('rawHash');
        $reflection_property->setAccessible(true);
        $reflection_property->setValue(
            $workflowConfigDto,
            [
                "name" => "foo",
                "storage" => [
                    "adapter" => "elasticsearch",
                    "config" => []
                ]
            ]
        );
        $this->expectException(Storage\StorageException::class);
        $adapter = Storage\StorageAdapterFactory::get(
            $workflowConfigDto,
            Storage\StorageAdapterFactory::USAGE_MONITORING
        );
    }

}
