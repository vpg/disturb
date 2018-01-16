<?php

namespace Tests\Library\Core\Storage;

use \Vpg\Disturb\Workflow;


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
}
