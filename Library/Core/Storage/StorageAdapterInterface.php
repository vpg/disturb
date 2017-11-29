<?php

namespace Vpg\Disturb\Core\Storage;

use \Phalcon\Config;

/**
 * Interface ContextStorageAdapterInterface
 *
 * @package  Disturb\Core\Storage
 * @author   Alexandre DEFRETIN <adefretin@voyageprive.com>
 * @license  https://github.com/vpg/disturb/blob/master/LICENSE MIT Licence
 */
interface StorageAdapterInterface
{
    /**
     * Initialize
     *
     * @param Config $config config
     * @param string $usage  define the usage, could either be context or monitoring
     *
     * @return mixed
     */
    public function initialize(Config $config, string $usage);

    /**
     * Get storage data identified by $id
     *
     * @param string $id workflowProcessorId
     *
     * @return mixed
     */
    public function get(string $id);

    /**
     * Search storage data identified by $queryHash
     *
     * @param array $queryHash queryHash
     *
     * @return mixed
     */
    public function search(array $queryHash);

    /**
     * Check if storage date id exists
     *
     * @param string $id workflowProcessorId
     *
     * @return bool
     */
    public function exists(string $id) : bool;

    /**
     * Save storage data identified by $id
     *
     * @param string $id        id
     * @param array  $valueHash valueHash
     *
     * @return mixed
     */
    public function save(string $id, array $valueHash);

    /**
     * Delete storage data identified by $id
     *
     * @param string $id workflowProcessorId
     *
     * @return mixed
     */
    public function delete(string $id);
}
