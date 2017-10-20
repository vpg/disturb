<?php

namespace Vpg\Disturb\ContextStorageAdapters;

use \Phalcon\Config\Adapter\Json;

/**
 * Interface ContextStorageAdapterInterface
 *
 * @package Disturb\ContextStorageAdapters
 */
interface ContextStorageAdapterInterface
{
    /**
     * Initialize
     *
     * @param Json $config
     *
     * @return mixed
     */
    public function initialize(Json $config);

    /**
     * Get storage data identified by key
     *
     * @param string $key
     *
     * @return mixed
     */
    public function get(string $key);

    /**
     * Check if storage date key exists
     *
     * @param string $key
     *
     * @return bool
     */
    public function exist(string $key) : bool;

    /**
     * Save storage data identified by key
     *
     * @param string $key
     * @param array $valueHash
     *
     * @return mixed
     */
    public function save(string $key, array $valueHash);

    /**
     * Delete storage data identified by key
     *
     * @param string $key
     *
     * @return mixed
     */
    public function delete(string $key);
}
