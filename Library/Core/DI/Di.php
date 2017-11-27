<?php

namespace Vpg\Disturb\Core\DI;

/**
 * Class DI
 *
 * @package Vpg\Disturb\Core\DI
 * @author  Thomas Pellegatta <tpellegatta@voyageprive.com>
 *
 */
class Di extends \Phalcon\Di
{
    const DISTURB_PREFIX = 'disturb-';

    public function get($name, $parameters = null)
    {
        return parent::get(self::DISTURB_PREFIX.$name, $parameters);
    }

    public function set($name, $definition, $shared = false) {
        parent::set(self::DISTURB_PREFIX.$name, $definition, $shared);
    }
}