<?php
namespace Vpg\Disturb\Cli;

use Phalcon\Cli;

/**
 * Console
 * Provides generic func related to PHP CLI
 *
 * @package    Disturb
 * @subpackage Cli
 */
class Console extends Cli\Console {

    /**
     * @const string OPT_KEYS_GROUP_NAME
     */
    const OPT_KEYS_GROUP_NAME = 'optKeys';

    /**
     * @const string OPT_VALS_GROUP_NAME
     */
    const OPT_VALS_GROUP_NAME = 'optVals';

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
        preg_match_all(
            '/--(?P<' . self::OPT_KEYS_GROUP_NAME . '>\w+)(?:=(?P<' . self::OPT_VALS_GROUP_NAME . '>[^ ]*))?/',
            $argv,
            $paramMatchHash
        );
        $paramHash = array_combine(array_values($paramMatchHash['optKeys']), array_values($paramMatchHash['optVals']));
        return $paramHash;
    }
}
