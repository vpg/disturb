<?php

namespace Vpg\Disturb\Workflow;

/**
 * ManagerServiceInterface
 *
 * @package  Disturb\Workflow
 * @author   Jérome BOURGEAIS <jbourgeais@voyageprive.com>
 * @license  https://github.com/vpg/disturb/blob/master/LICENSE MIT Licence
 */
interface ManagerServiceInterface
{
    /**
     * Returns the input data related to the given step for the related workflow
     *
     * @param string $workflowProcessId The workflow process identifier
     * @param string $stepCode          The step code related to the step to finalize
     *
     * @return array the step to run input data
     *
     * @throws Vpg\Disturb\Workflow\WorkflowException
     */
    public function getStepInput(string $workflowProcessId, string $stepCode) : array;
}
