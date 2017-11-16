<?php

namespace Vpg\Disturb\Services;

/**
 * StepServiceInterface
 *
 * @category Services
 * @package  Disturb\Services
 * @author   @author   Jérome BOURGEAIS <jbourgeais@voyageprive.com>
 * @license  https://github.com/vpg/disturb/blob/master/LICENSE MIT Licence
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

