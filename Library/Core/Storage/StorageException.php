<?php

namespace Vpg\Disturb\Core\Storage;

use Vpg\Disturb\Core;

/**
 * Class StorageException
 *
 * @package  Disturb\Core\Storage
 * @author   Alexandre DEFRETIN <adefretin@voyageprive.com>
 * @license  https://github.com/vpg/disturb/blob/master/LICENSE MIT Licence
 */
class StorageException extends Core\Exception
{
    /**
     * Code adapter const
     *
     * @const int CODE_ADAPTER
     */
    const CODE_ADAPTER = 0;

    /**
     * Code config const
     *
     * @const int CODE_CONFIG
     */
    const CODE_CONFIG = 1;

    /**
     * Code vendor const
     *
     * @const int CODE_VENDOR
     */
    const CODE_VENDOR = 2;

    /**
     * Code invalid parameter const
     *
     * @const int CODE_INVALID_PARAMETER
     */
    const CODE_INVALID_PARAMETER = 3;

    /**
     * Code get const
     *
     * @const int CODE_GET
     */
    const CODE_GET = 4;

    /**
     * Code exist const
     *
     * @const int CODE_EXIST
     */
    const CODE_EXIST = 5;

    /**
     * Code save const
     *
     * @const int CODE_SAVE
     */
    const CODE_SAVE = 6;

    /**
     * Code delte const
     *
     * @const int CODE_DELETE
     */
    const CODE_DELETE = 7;
}
