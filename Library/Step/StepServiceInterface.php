<?php
namespace Vpg\Disturb\Step;

/**
 * StepServiceInterface
 *
 * @package  Disturb\Step
 * @author   Jérome BOURGEAIS <jbourgeais@voyageprive.com>
 * @license  https://github.com/vpg/disturb/blob/master/LICENSE MIT Licence
 */

interface StepServiceInterface
{
    /**
     * Execute step
     *
     * @param array $paramHash paramHash
     *
     * @return array
     */
    public function execute(array $paramHash) : array;

    /**
     * Called before the step execution
     *
     * @param array $paramHash The input hash given to the execute func
     *
     * @return void
     */
    public function beforeExecute(array $paramHash);

    /**
     * Called after the step execution
     *
     * @param array $paramHash  The input hash given to the execute func
     * @param array $resultHash The output hash returned by the execute func
     *
     * @return void
     */
    public function afterExecute(array $paramHash, array $resultHash);
}

