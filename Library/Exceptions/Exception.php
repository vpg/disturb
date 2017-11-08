<?php

namespace Vpg\Disturb\Exceptions;

use Throwable;

abstract class Exception extends \Phalcon\Exception {

    /**
     * Exception constructor
     *
     * @param string $message
     * @param int $code
     * @param Throwable|null $previous
     * @param int $callingFunctionNestedLevel
     */
    public function __construct(
        $message = '',
        $code = 0,
        Throwable $previous = null,
        $callingFunctionNestedLevel = 1
    )
    {
        $backTraceHash = debug_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS, $callingFunctionNestedLevel + 1);
        $callingFunction = $backTraceHash[$callingFunctionNestedLevel]['class'] . '::' .
            $backTraceHash[$callingFunctionNestedLevel]['function'];
        $message = $callingFunction . ' : ' . $message;

        parent::__construct($message, $code, $previous);

        // TODO use logger ?
    }

}