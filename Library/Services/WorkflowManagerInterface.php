<?php

/**
 * WorkflowManagerInterface
 *
 * @category Services
 * @package  Disturb\Services
 * @author   Jérome BOURGEAIS <jbourgeais@voyageprive.com>
 * @license  https://github.com/vpg/disturb/blob/poc/LICENSE MIT Licence
 * @version  0.1.0
 * @link     http://example.com/my/bar Documentation of Foo.
 */

namespace Vpg\Disturb\Services;

/**
 * Interface WorkflowManagerInterface
 *
 * @category Services
 * @package  Disturb\Services
 * @author   Jérome BOURGEAIS <jbourgeais@voyageprive.com>
 * @license  https://github.com/vpg/disturb/blob/poc/LICENSE MIT Licence
 * @version  0.1.0
 * @link     http://example.com/my/bar Documentation of Foo.
 */
interface WorkflowManagerInterface
{

    /**
     * Constructor
     *
     * @param string $workflowConfigFilePath workflow config file path
     */
    public function __construct(string $workflowConfigFilePath);

    /**
     * Initializes the workflow for the given process identifier
     *
     * @param string $workflowProcessId The workflow process identifier
     *
     * @return void
     * @throws Vpg\Disturb\Exceptions|WorkflowException
     */
    public function init(string $workflowProcessId);

    /**
     * Returns the current status of the workflow for the given process identifier
     *
     * @param string $workflowProcessId The workflow process identifier
     *
     * @return string the workflow status code
     * @throws Vpg\Disturb\Exceptions\WorkflowException
     */
    public function getStatus(string $workflowProcessId) : string;

    /**
     * Returns the next step info to run for current  workflow related to the given process identifier
     *
     * @param string $workflowProcessId The workflow process identifier
     *
     * @return array the next workflow step hash
     * @throws Vpg\Disturb\Exceptions\WorkflowException
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
     * @throws \Disturb\WorkflowException
     */
    public function processStepJobResult(string $workflowProcessId, string $stepCode, int $jobId, array $resultHash);

}
