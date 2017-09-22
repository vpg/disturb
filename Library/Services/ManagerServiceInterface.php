<?php
namespace Disturb\Services;

interface ManagerServiceInterface
{
    /**
     * Returns the input data related to the given step for the related workflow
     *
     * @param string $workflowProcessId The workflow process identifier
     * @param string $stepCode          The step code related to the step to finalize
     *
     * @return array the step to run input data
     * @throws \Disturb\WorkflowException
     */
    public function getStepInput(string $workflowProcessId, string $stepCode) : array;
}
