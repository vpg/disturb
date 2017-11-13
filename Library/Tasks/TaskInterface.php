<?php

/**
 * Task
 *
 * @category Tasks
 * @package  Disturb\Tasks
 * @author   Jérome BOURGEAIS <jbourgeais@voyageprive.com>
 * @license  https://github.com/vpg/disturb/blob/poc/LICENSE MIT Licence
 * @version  0.1.0
 * @link     http://example.com/my/bar Documentation of Foo.
 */
namespace Vpg\Disturb\Tasks;

/**
 * Interface Task
 *
 * @category Tasks
 * @package  Disturb\Tasks
 * @author   Jérome BOURGEAIS <jbourgeais@voyageprive.com>
 * @license  https://github.com/vpg/disturb/blob/poc/LICENSE MIT Licence
 * @version  0.1.0
 * @link     http://example.com/my/bar Documentation of Foo.
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

