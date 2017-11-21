<?php

namespace Vpg\Disturb\Core;

/**
 * Interface Task
 *
 * @category Tasks
 * @package  Disturb\Tasks
 * @author   Jérome BOURGEAIS <jbourgeais@voyageprive.com>
 * @license  https://github.com/vpg/disturb/blob/master/LICENSE MIT Licence
 */
interface TaskInterface
{
    /**
     * Start task action
     *
     * @param array $paramHash the params
     *
     * @return void
     */
    public function startAction(array $paramHash);
}

