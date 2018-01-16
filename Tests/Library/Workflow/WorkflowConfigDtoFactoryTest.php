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
     * Test wrong file ext
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
        $this->assertEquals($workflowConfigDto->getWorkflowName(), 'loadingContract');
        // invalid input type
        $this->expectException(Dto\InvalidInputTypeException::class);
        $workflowConfigDto = new Workflow\WorkflowConfigDto(true);
    }
}
