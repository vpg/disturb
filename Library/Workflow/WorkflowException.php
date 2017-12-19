<?php

namespace Vpg\Disturb\Workflow;

use Vpg\Disturb\Core;

/**
 * Class WorkflowException
 *
 * @package  Disturb\Workflow
 * @author   Maxime BRENGUIER <mbrenguier@voyageprive.com>
 * @license  https://github.com/vpg/disturb/blob/master/LICENSE MIT Licence
 */
class WorkflowException extends Core\Exception
{
    /**
     * Code manager class not found const
     *
     * @const int CODE_MANAGER_CLASS_NOT_FOUND
     */
    const CODE_MANAGER_CLASS_NOT_FOUND = 0;
}
