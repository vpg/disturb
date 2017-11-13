<?php

/**
 * Exception
 *
 * @category Exceptions
 * @package  Disturb\Exceptions
 * @author   Alexandre DEFRETIN <adefretin@voyageprive.com>
 * @license  https://github.com/vpg/disturb/blob/poc/LICENSE MIT Licence
 * @version  0.1.0
 * @link     http://example.com/my/bar Documentation of Foo.
 */

namespace Vpg\Disturb\Exceptions;

use Throwable;

/**
 * Abstract class Exception
 *
 * @category Exceptions
 * @package  Disturb\Exceptions
 * @author   Alexandre DEFRETIN <adefretin@voyageprive.com>
 * @license  https://github.com/vpg/disturb/blob/poc/LICENSE MIT Licence
 * @version  0.1.0
 * @link     http://example.com/my/bar Documentation of Foo.
 */
abstract class Exception extends \Phalcon\Exception
{

    /**
     * Exception constructor
     *
     * @param string         $message                    exception message
     * @param int            $code                       exception code
     * @param Throwable|null $previous                   previous
     * @param int            $callingFunctionNestedLevel nested function level
     */
    public function __construct(
        $message = '',
        $code = 0,
        Throwable $previous = null,
        $callingFunctionNestedLevel = 1
    ) {
        $backTraceHash = debug_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS, $callingFunctionNestedLevel + 1);
        $callingFunction = $backTraceHash[$callingFunctionNestedLevel]['class'] . '::' .
            $backTraceHash[$callingFunctionNestedLevel]['function'];
        $message = $callingFunction . ' : ' . $message;

        parent::__construct($message, $code, $previous);

        // TODO use logger ?
    }

}