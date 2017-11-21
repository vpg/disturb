<?php

namespace Vpg\Disturb\Services;

/**
 * ManagerServiceInterface
 *
 * @category Services
 * @package  Disturb\Services
 * @author   Jérome BOURGEAIS <jbourgeais@voyageprive.com>
 * @license  https://github.com/vpg/disturb/blob/master/LICENSE MIT Licence
 * @link     http://example.com/my/bar Documentation of Foo.
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
     * @throws Vpg\Disturb\WorkflowException
     */
    public function getStepInput(string $workflowProcessId, string $stepCode) : array;
}
