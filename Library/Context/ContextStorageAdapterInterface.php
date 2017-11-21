<?php

namespace Vpg\Disturb\Context;

use \Phalcon\Config;

/**
 * Interface ContextStorageAdapterInterface
 *
 * @package  Disturb\Context
 * @author   Alexandre DEFRETIN <adefretin@voyageprive.com>
 * @license  https://github.com/vpg/disturb/blob/master/LICENSE MIT Licence
 */
interface ContextStorageAdapterInterface
{
    /**
     * Initialize
     *
     * @param Config $config config
     *
     * @return mixed
     */
    public function initialize(Config $config);

    /**
     * Get storage data identified by $workflowProcessId
     *
     * @param string $workflowProcessId workflowProcessorId
     *
     * @return mixed
     */
    public function get(string $workflowProcessId);

    /**
     * Search storage data identified by $queryHash
     *
     * @param array $queryHash queryHash
     *
     * @return mixed
     */
    public function search(array $queryHash);

    /**
     * Check if storage date $workflowProcessId exists
     *
     * @param string $workflowProcessId workflowProcessorId
     *
     * @return bool
     */
    public function exist(string $workflowProcessId) : bool;

    /**
     * Save storage data identified by $workflowProcessId
     *
     * @param string $workflowProcessId workflowProcessorId
     * @param array  $valueHash         valueHash
     *
     * @return mixed
     */
    public function save(string $workflowProcessId, array $valueHash);

    /**
     * Delete storage data identified by $workflowProcessId
     *
     * @param string $workflowProcessId workflowProcessorId
     *
     * @return mixed
     */
    public function delete(string $workflowProcessId);
}
