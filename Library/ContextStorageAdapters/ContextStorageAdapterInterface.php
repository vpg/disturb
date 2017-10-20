<?php

namespace Disturb\ContextStorageAdapters;

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
     * @param \Phalcon\Config\Adapter\Json $config
     *
     * @return mixed
     */
    public function initialize(\Phalcon\Config\Adapter\Json $config);

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
