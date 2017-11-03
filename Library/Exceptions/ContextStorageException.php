<?php

namespace Vpg\Disturb\Exceptions;

use Throwable;

/**
 * Class ContextStorageException
 *
 * @package Disturb\ContextStorage
 */
class ContextStorageException extends \Phalcon\Exception
{
    /**
     * @const int CODE_GENERIC
     */
    const CODE_GENERIC = 0;

    /**
     * @const int CODE_CONFIG
     */
    const CODE_CONFIG = 1;

    /**
     * @const int CODE_VENDOR
     */
    const CODE_VENDOR = 2;

    /**
     * @const int CODE_INVALID_PARAMETER
     */
    const CODE_INVALID_PARAMETER = 3;

    /**
     * @const int CODE_GET
     */
    const CODE_GET = 4;

    /**
     * @const int CODE_EXIST
     */
    const CODE_EXIST = 5;

    /**
     * @const int CODE_SAVE
     */
    const CODE_SAVE = 6;

    /**
     * @const int CODE_DELETE
     */
    const CODE_DELETE = 7;

    /**
     * ContextStorageException constructor
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