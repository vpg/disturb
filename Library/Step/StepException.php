<?php

namespace Vpg\Disturb\Step;

use Vpg\Disturb\Core;

/**
 * Class StepException
 *
 * @package  Disturb\Step
 * @author   Thomas PELLEGATTA <tpellegatta@voyageprive.com>
 * @license  https://github.com/vpg/disturb/blob/master/LICENSE MIT Licence
 */
class StepException extends Core\Exception
{
    /**
     * Code manager class not found const
     *
     * @const int CODE_MANAGER_CLASS_NOT_FOUND
     */
    const CODE_STEP_CLASS_NOT_FOUND = 0;
}
