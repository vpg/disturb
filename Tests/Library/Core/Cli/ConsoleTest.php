<?php

namespace Tests\Library\Cli;

use Vpg\Disturb\Core\Cli;


/**
 * CLI Console test class
 *
 * @author  JEROME BOURGEAIS <jbourgeais@voyageprive.com>
 */
class consoleTest extends \Tests\DisturbUnitTestCase
{

    /**
     * Test parseLongOpt method
     *
     * @return void
     */
    public function testParseLongOpt()
    {
        $argv = "--foo --bar=gar";
        $paramHash = Cli\Console::parseLongOpt($argv);
        $this->assertEquals(
            [
                'foo' => '',
                'bar' => 'gar'
            ],
            $paramHash
        );
    }
}
