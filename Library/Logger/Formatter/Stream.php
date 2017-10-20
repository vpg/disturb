<?php

namespace Disturb\Logger\Formatter;

use \Phalcon\Logger\FormatterInterface;

class Stream implements FormatterInterface
{
    const LOG_SEPARATOR = ';';
    const LOG_DATE_FORMAT = 'Y-m-d H:i:s';

    /**
     * Const log type
     *
     * @var array
     *
     * @see http://docs.phalconphp.com/en/latest/api/Phalcon_Logger.html
     */
    private $constType = [
        'emergence',
        'critical',
        'alert',
        'error',
        'warning',
        'notice',
        'info',
        'debug',
        'custom',
        'special'
    ];

    /**
     * Const log type color
     *
     * @var array
     *
     * 0 = none
     * 30 = black
     * 31 = red
     * 32 = green
     * 33 = yellow
     * 34 = blue
     * 35 = magenta
     * 36 = cyan
     * 37 = white
     */
    private $constTypeColor = [
        '31', // emergence
        '31', // critical
        '31', // alert
        '31', // error
        '33', // warning
        '0', // notice
        '32', // info
        '0', // debug
        '0', // custom
        '0'  // special
    ];

    /**
     * Format log
     *
     * @param string $message   log message
     * @param int    $type      log type
     * @param int    $timestamp log date
     * @param array  $context   log context
     *
     * @return string
     */
    public function format($message, $type, $timestamp, $context = null)
    {
        $dateLog = new \DateTime();
        $dateLog->setTimestamp($timestamp);

        return $dateLog->format(self::LOG_DATE_FORMAT) .
            " \033[" . $this->constTypeColor[$type] . "m[" . strtoupper($this->constType[$type]) . "]\033[" . $this->constTypeColor[$type] . "m" . "\033[0m" .
            $message.
            "\n";
    }
}
