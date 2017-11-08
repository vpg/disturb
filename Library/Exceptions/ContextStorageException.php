<?php

namespace Vpg\Disturb\Exceptions;

/**
 * Class ContextStorageException
 *
 * @package Disturb\ContextStorage
 */
class ContextStorageException extends Exception
{
    /**
     * @const int CODE_ADAPTER
     */
    const CODE_ADAPTER = 0;

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
}