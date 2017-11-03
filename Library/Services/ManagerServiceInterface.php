<?php
/**
 * ManagerServiceInterface
 *
 * Provides helper functions that act upon a token array and modify the file
 * content.
 *
 * @category Test
 * @package  Disturb
 * @author   Maxime BRENGUIER <mbrenguier@voyageprive.com>
 * @license  https://github.com/vpg/disturb/blob/poc/LICENSE MIT Licence
 * @version  0.0.1
 * @link     http://example.com/my/bar Documentation of Foo.
 */


namespace Vpg\Disturb\Services;

/**
 * ManagerServiceInterface
 *
 * @category Test
 * @package  Disturb
 * @author   Maxime BRENGUIER <mbrenguier@voyageprive.com>
 * @license  https://github.com/vpg/disturb/blob/poc/LICENSE MIT Licence
 * @version  0.0.1
 * @link     http://example.com/my/bar Documentation of Foo.
 * @see      \Disturb\Services\ManagerServiceInterface
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
     * @throws Vpg\Disturb\WorkflowException
     */
    public function getStepInput(string $workflowProcessId, string $stepCode) : array;
}
