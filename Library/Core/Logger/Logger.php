<?php

namespace Vpg\Disturb\Core\Logger;

/**
 * Class Logger
 *
 * @package  Disturb\Core\Logger
 * @author   Maxime BRENGUIER <mbrenguier@voyageprive.com>
 * @license  https://github.com/vpg/disturb/blob/master/LICENSE MIT Licence
 */
class Logger extends \Phalcon\Logger\Multiple
{
    /**
     * Debug log
     *
     * @param string $message Message to be logged
     * @param array  $context Context
     *
     * @return void
     */
    public function debug($message, array $context = null)
    {
        if (defined('DISTURB_DEBUG') && DISTURB_DEBUG == true) {
            $message = $this->prefixMessage($message);
            parent::debug($message, $context);
        }
    }

    /**
     * Info log
     *
     * @param string $message Message to be logged
     * @param array  $context Context
     *
     * @return void
     */
    public function info($message, array $context = null)
    {
        $message = $this->prefixMessage($message);
        parent::info($message, $context);
    }

    /**
     * Error log
     *
     * @param string $message Message to be logged
     * @param array  $context Context
     *
     * @return void
     */
    public function error($message, array $context = null)
    {
        $message = $this->prefixMessage($message);
        parent::error($message, $context);
    }

    /**
     * Warning log
     *
     * @param string $message Message to be logged
     * @param array  $context Context
     *
     * @return void
     */
    public function warning($message, array $context = null)
    {
        $message = $this->prefixMessage($message);
        parent::warning($message, $context);
    }

    /**
     * Prefixing message with class and function called
     *
     * @param string $message Message to be logged
     *
     * @return string
     */
    public function prefixMessage($message)
    {
        if (defined('DISTURB_DEBUG') && DISTURB_DEBUG == true) {
            $debugBacktraceHash = debug_backtrace(DEBUG_BACKTRACE_PROVIDE_OBJECT, 2);

            if (!empty($debugBacktraceHash[2])) {
                $message = '> ' .
                    $debugBacktraceHash[2]['class'] .
                    '\\' .
                    $debugBacktraceHash[2]['function'] .
                    ' : ' .
                    $message;
            }
        }
        return $message;
    }
}
