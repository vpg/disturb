<?php

/**
 * StepServiceInterface
 *
 * @category Services
 * @package  Disturb\Services
 * @author   @author   Jérome BOURGEAIS <jbourgeais@voyageprive.com>
 * @license  https://github.com/vpg/disturb/blob/poc/LICENSE MIT Licence
 * @version  0.1.0
 * @link     http://example.com/my/bar Documentation of Foo.
 */

namespace Vpg\Disturb\Services;

/**
 * StepServiceInterface
 *
 * @category Services
 * @package  Disturb\Services
 * @author   @author   Jérome BOURGEAIS <jbourgeais@voyageprive.com>
 * @license  https://github.com/vpg/disturb/blob/poc/LICENSE MIT Licence
 * @version  0.1.0
 * @link     http://example.com/my/bar Documentation of Foo.
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

