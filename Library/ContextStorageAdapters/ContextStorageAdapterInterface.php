<?php

namespace Vpg\Disturb\ContextStorageAdapters;

use \Phalcon\Config;

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
     * @param Config $config
     *
     * @return mixed
     */
    public function initialize(Config $config);

    /**
     * Get storage data identified by $workflowProcessId
     *
     * @param string $workflowProcessId
     *
     * @return mixed
     */
    public function get(string $workflowProcessId);

    /**
     * Search storage data identified by $queryHash
     *
     * @param array $queryHash
     *
     * @return mixed
     */
    public function search(array $queryHash);

    /**
     * Check if storage date $workflowProcessId exists
     *
     * @param string $workflowProcessId
     *
     * @return bool
     */
    public function exist(string $workflowProcessId) : bool;

    /**
     * Save storage data identified by $workflowProcessId
     *
     * @param string $workflowProcessId
     * @param array $valueHash
     *
     * @return mixed
     */
    public function save(string $workflowProcessId, array $valueHash);

    /**
     * Delete storage data identified by $workflowProcessId
     *
     * @param string $workflowProcessId
     *
     * @return mixed
     */
    public function delete(string $workflowProcessId);
}
