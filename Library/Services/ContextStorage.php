<?php

namespace Disturb\Services;

use Disturb\ContextStorageAdapters;
use Disturb\Exception;

class ContextStorage
{
    /**
     * @var \Disturb\ContextStorageAdapters\ContextStorageAdapterInterface $adapter
     */
    private $adpater;

    /**
     * ContextStorage constructor
     *
     * @param \Phalcon\Config\Adapter\Json $config
     *
     * @throws Exception
     */
    public function __construct(\Phalcon\Config\Adapter\Json $config)
    {
        echo "DEBUG = " . __FILE__ . " => " . __METHOD__ . " => " . __LINE__;
        echo "<pre>";
        echo var_dump('HERE');
        echo "</pre>";
        die('END DEBUG');

        // check adapter type
        if (empty($config->adapter)) {
            throw new Exception('Context Storage adapter name not found');
        }

        // check if adapter class exists
        $adapterClass = ucfirst($config->adapter) . 'Adapter';
        if (! class_exists($adapterClass)) {
            throw new Exception('Context Storage adapter class not found');
        }

        // check if adapter config exists
        if (empty($config->config)) {
            throw new Exception('Context Storage adapter config not found');
        }

        $this->adpater = new $adapterClass();
        $this->adpater->initialize($config->config);
    }

    /**
     * Get storage data identified by key
     *
     * @param string $key
     *
     * @return mixed
     */
    public function get(string $key) {
        return $this->adpater->get($key);
    }

    /**
     * Check if storage date key exists
     *
     * @param string $key
     *
     * @return bool
     */
    public function exist(string $key) {
        return $this->adpater->exist($key);
    }

    /**
     * Save storage data identified by key
     *
     * @param string $key
     * @param array $valueHash
     *
     * @return mixed
     */
    public function save(string $key, array $valueHash) {
        return $this->adpater->save($key, $valueHash);
    }

    /**
     * Delete storage data identified by key
     *
     * @param string $key
     *
     * @return mixed
     */
    public function delete(string $key) {
        return $this->adpater->delete($key);
    }
}