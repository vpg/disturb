<?php
namespace Disturb\Services;

interface WorkflowManagerInterface
{

    public function __construct(string $workflowConfigFilePath);

    /**
     * Initializes the workflow for the given process identifier
     *
     * @param string $workflowProcessId The workflow process identifier
     *
     * @return void
     * @throws \Disturb\WorkflowException
     */
    public function init(string $workflowProcessId);

    /**
     * Returns the current status of the workflow for the given process identifier
     *
     * @param string $workflowProcessId The workflow process identifier
     *
     * @return string the workflow status code
     * @throws \Disturb\WorkflowException
     */
    public function getStatus(string $workflowProcessId) : string;

    /**
     * Returns the next step info to run for current  workflow related to the given process identifier
     *
     * @param string $workflowProcessId The workflow process identifier
     *
     * @return array the next workflow step hash
     * @throws \Disturb\WorkflowException
     */
    public function getNextStepTaskList(string $workflowProcessId) : array;

    /**
     * Finalizes the given step for the workflow related to the given process identifier
     *
     * @param string $workflowProcessId The workflow process identifier
     * @param string $stepCode          The step code related to the step to finalize
     * @param array  $resultHash        The result info returned by the step to finalize
     *
     * @return void
     * @throws \Disturb\WorkflowException
     */
    public function finalizeStep(string $workflowProcessId, string $stepCode, array $resultHash);

}
