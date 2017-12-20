<?php

namespace Vpg\Disturb\Workflow;

/**
 * Interface WorkflowManagerInterface
 *
 * @package  Disturb\Workflow
 * @author   Jérome BOURGEAIS <jbourgeais@voyageprive.com>
 * @license  https://github.com/vpg/disturb/blob/master/LICENSE MIT Licence
 */
interface WorkflowManagerInterface
{
    /**
     * Constructor
     *
     * @param WorkflowConfigDto $workflowConfigDto workflow config
     */
    public function __construct(WorkflowConfigDto $workflowConfigDto);

    /**
     * Initializes the workflow for the given process identifier
     *
     * @param string $workflowProcessId The workflow process identifier
     * @param array  $payloadHash       the workflow initial payload
     * @param string $workerHostname    the worker on which the WF has been init
     *
     * @return void
     * @throws \Vpg\Disturb\Workflow\WorkflowException
     */
    public function init(string $workflowProcessId, array $payloadHash, string $workerHostname);

    /**
     * Returns the current status of the workflow for the given process identifier
     *
     * @param string $workflowProcessId The workflow process identifier
     *
     * @return string the workflow status code
     * @throws \Vpg\Disturb\Workflow\WorkflowException
     */
    public function getStatus(string $workflowProcessId) : string;

    /**
     * Returns the next step info to run for current  workflow related to the given process identifier
     *
     * @param string $workflowProcessId The workflow process identifier
     *
     * @return array the next workflow step hash
     * @throws \Vpg\Disturb\Workflow\WorkflowException
     */
    public function getNextStepList(string $workflowProcessId) : array;

    /**
     * Finalizes the given step for the workflow related to the given process identifier
     *
     * @param string $workflowProcessId The workflow process identifier
     * @param string $stepCode          The step code related to the step to finalize
     * @param int    $jobId             The job id related to the processed job
     * @param array  $resultHash        The result info returned by the step to finalize
     *
     * @return void
     * @throws \Vpg\Disturb\Workflow\WorkflowException
     */
    public function processStepJobResult(string $workflowProcessId, string $stepCode, int $jobId, array $resultHash);

}
