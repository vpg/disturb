<?php

namespace Vpg\Disturb\Core;

/**
 * Interface Worker
 *
 * @package  Disturb\Core
 * @author   Jérome BOURGEAIS <jbourgeais@voyageprive.com>
 * @license  https://github.com/vpg/disturb/blob/master/LICENSE MIT Licence
 */
interface WorkerInterface
{
    /**
     * Start worker action
     *
     * @param array $paramHash the params
     *
     * @return void
     */
    public function startAction(array $paramHash);
}

