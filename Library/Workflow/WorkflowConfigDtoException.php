<?php

namespace Vpg\Disturb\Workflow;

use Vpg\Disturb\Core;

/**
 * Class WorkflowConfigDtoException
 *
 * @package  Disturb\Workflow
 * @license  https://github.com/vpg/disturb/blob/master/LICENSE MIT Licence
 */
class WorkflowConfigDtoException extends Core\Exception
{
    /**
     * Code workflow config file not found
     *
     * @const int CODE_NOT_FOUND
     */
    const CODE_NOT_FOUND = 0;

    /**
     * Code workflow config file with un authorized ext
     *
     * @const int CODE_CONFIG
     */
    const CODE_BAD_EXT = 1;
}
