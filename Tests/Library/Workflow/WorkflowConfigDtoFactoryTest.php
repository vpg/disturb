<?php

namespace Tests\Library\Workflow;

use \Vpg\Disturb\Workflow;
use \Vpg\Disturb\Core\Dto;


/**
 * WF DTO factory test class
 *
 * @author  JEROME BOURGEAIS <jbourgeais@voyageprive.com>
 */
class WorkflowConfigDtoFactoryTest extends \Tests\DisturbUnitTestCase
{

    /**
     * Test missing file
     *
     * @return void
     */
    public function testWrongFile()
    {
        $this->expectException(Workflow\WorkflowConfigDtoException::class);
        $workflowConfigDto = Workflow\WorkflowConfigDtoFactory::get(realpath(__DIR__ . '/../../Config/foo.json'));
    }

    /**
     * Test wrong file ext
     *
     * @return void
     */
    public function testWrongFileExt()
    {
        $this->expectException(Workflow\WorkflowConfigDtoException::class);
        $workflowConfigDto = Workflow\WorkflowConfigDtoFactory::get(
            realpath(__DIR__ . '/../../Config/InvalidWorkflowConfig-WrongExt.foo')
        );
    }

    /**
     * Test dto input
     *
     * @return void
     */
    public function testDTOInput()
    {
        $workflowConfigDto = new Workflow\WorkflowConfigDto(
            '{"name" : "json", "storage" : {"config":{"host":"foo"}}}'
        );
        $this->assertEquals($workflowConfigDto->getWorkflowName(), 'json');
        $workflowConfigDto = new Workflow\WorkflowConfigDto(
            realpath(__DIR__ . '/../../Config/serie.json')
        );
        $this->assertEquals($workflowConfigDto->getWorkflowName(), 'test');
        // invalid input type
        $this->expectException(Dto\InvalidInputTypeException::class);
        $workflowConfigDto = new Workflow\WorkflowConfigDto(true);
    }

    /**
     * Test dto missing prop
     *
     * @return void
     */
    public function testDTOMissingProps()
    {
        $this->expectException(Workflow\InvalidWorkflowConfigException::class);
        $workflowConfigDto = new Workflow\WorkflowConfigDto(
            '{"name" : "json", "storage" : {"config":{"host":""}}}'
        );
    }

    /**
     * Test dto missing array prop
     *
     * @return void
     */
    public function testDTOMissingArrayProps()
    {
        $this->expectException(Workflow\InvalidWorkflowConfigException::class);
        $workflowConfigDto = new Workflow\WorkflowConfigDto(
            '{"name" : "foo"}'
        );
    }

    /**
     * Test dto  props
     *
     * @return void
     */
    public function testDTOProps()
    {
        $workflowConfigDto = new Workflow\WorkflowConfigDto(
            realpath(__DIR__ . '/../../Config/serie.json')
        );
        $this->assertEquals(
            $workflowConfigDto->getBrokerServerList(),
            ['10.13.11.27', '10.13.11.28', '10.13.11.29']
        );
        $this->assertEquals($workflowConfigDto->getServicesClassPath(), './Tests/Mocks/Client/');
        $this->assertEquals($workflowConfigDto->getServicesClassNameSpace(), 'Vpg\Disturb\Test');
        $this->assertEquals($workflowConfigDto->getStorageHost(), 'http://vp-aix-elsdisturb.aix.vpg.lan:9200');
    }
}
