<?php

namespace Vpg\Disturb\Services;

use Vpg\Disturb\ContextStorageAdapters;
use Vpg\Disturb\Exceptions\ContextStorageException;

use \Phalcon\Config\Adapter\Json;

class ContextStorage
{
    /**
     * @var ContextStorageAdapterInterface $adapter
     */
    private $adpater;

    /**
     * ContextStorage constructor
     *
     * @param Json $config
     *
     * @throws ContextStorageException
     */
    public function __construct(Json $config)
    {
        // check adapter type
        if (empty($config->adapter)) {
            throw new ContextStorageException('Adapter name not found');
        }

        // check if adapter class exists
        $adapterClass = ucfirst($config->adapter) . 'Adapter';
        if (! class_exists($adapterClass)) {
            throw new ContextStorageException('Adapter class not found');
        }

        // check if adapter config exists
        if (empty($config->config)) {
            throw new ContextStorageException('Adapter config not found');
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