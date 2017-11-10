<?php
namespace Vpg\Disturb\Cli;

use Phalcon\Cli;

class Console extends Cli\Console {

    /**
     * Parses and validates the given argv according to the worker options config
     *
     * @param string $argv The argv list as string
     * @param array  $longOptKeyList The argv list
     *
     * @return array The parsed options hash
     */
    public static function parseLongOpt(string $argv)
    {
        preg_match_all('/--(?P<optKeys>\w+)(?:=(?P<optVals>[^ ]*))?/', $argv, $paramMatchHash);
        $paramHash = array_combine(array_values($paramMatchHash['optKeys']), array_values($paramMatchHash['optVals']));
        return $paramHash;
    }
}
