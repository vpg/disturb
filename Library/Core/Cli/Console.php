<?php
namespace Vpg\Disturb\Core\Cli;

use \Phalcon\Cli;

/**
 * Console
 * Provides generic func related to PHP CLI
 *
 * @category Cli
 * @package  Disturb\Cli
 * @author   Jérome BOURGEAIS <jbourgeais@voyageprive.com>
 * @license  https://github.com/vpg/disturb/blob/master/LICENSE MIT Licence
 * @link     http://example.com/my/bar Documentation of Foo.
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
        $paramHash = array_combine(array_values($paramMatchHash[self::OPT_KEYS_GROUP_NAME]), array_values(
            $paramMatchHash[self::OPT_VALS_GROUP_NAME])
        );
        return $paramHash;
    }
}
