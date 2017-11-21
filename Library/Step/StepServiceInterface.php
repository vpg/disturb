<?php
namespace Vpg\Disturb\Step;

/**
 * StepServiceInterface
 *
 * @package  Disturb\Step
 * @author   @author   Jérome BOURGEAIS <jbourgeais@voyageprive.com>
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
}

