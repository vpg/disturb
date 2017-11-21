<?php

namespace Vpg\Disturb\Core\Logger\Formatter;

use \Phalcon\Logger\FormatterInterface;

/**
 * Class Stream
 *
 * @category Formatter
 * @package  Disturb\Logger|Formatter
 * @author   Maxime BRENGUIER <mbrenguier@voyageprive.com>
 * @license  https://github.com/vpg/disturb/blob/master/LICENSE MIT Licence
 */
class Stream implements FormatterInterface
{
    const LOG_DATE_FORMAT = 'Y-m-d H:i:s';

    /**
     * Const type attributes
     *
     * @var array
     *
     * @see http://docs.phalconphp.com/en/latest/api/Phalcon_Logger.html
     *
     * 0 = none
     * 31 = red
     * 32 = green
     * 33 = yellow
     * 34 = blue
     */
    private $constType = [
        \Phalcon\Logger::INFO => [
            'label' => 'INFO', 'color' => '32'
        ],
        \Phalcon\Logger::WARNING => [
            'label' => 'WARNING', 'color' => '33'
        ],
        \Phalcon\Logger::DEBUG => [
            'label' => 'DEBUG', 'color' => '0'
        ],
        \Phalcon\Logger::ERROR => [
            'label' => 'ERROR', 'color' => '31'
        ]
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
            " \033[" . $this->constType[$type]['color'] . "m[" . $this->constType[$type]['label'] . "]\033[" .
            $this->constType[$type]['color'] . "m" . "\033[0m " .
            $message.
            "\n";
    }
}
