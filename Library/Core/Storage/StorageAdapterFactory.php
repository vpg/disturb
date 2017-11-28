<?php
namespace Vpg\Disturb\Core\Storage;

use \Phalcon\DI;

use Vpg\Disturb\Workflow;

/**
 * Class StorageAdapterFactory
 * provide a storage adapater instance according to the given conf
 *
 * @package  Disturb\Core\Storage
 * @author   Jérôme BOURGEAIS <jbourgeais@voyageprive.com>
 * @license  https://github.com/vpg/disturb/blob/master/LICENSE MIT Licence
 */
class StorageAdapterFactory
{
    /**
     * Elastic search adapter
     *
     * @const string ADAPTER_ELASTICSEARCH
     */
    const ADAPTER_ELASTICSEARCH = 'elasticsearch';


    /**
     * ContextStorage constructor
     *
     * @param Workflow\WorkflowConfigDto $config config
     * @param array                      $dbHash db config e.g. index and type for elasticsearch
     *
     * @throws StorageException
     *
     * @return StorageAdapterInterface implementation
     */
    public static function get(Workflow\WorkflowConfigDto $config, array $dbHash)
    {
        DI::getDefault()->get('logr')->debug(json_encode(func_get_args()));
        // check adapter type
        if (empty($config->getStorageAdapter())) {
            throw new StorageException(
                'Adapter name not found',
                StorageException::CODE_ADAPTER
            );
        }

        // check if adapter class exists
        switch ($config->getStorageAdapter()) {
            case self::ADAPTER_ELASTICSEARCH:
                $adapterClass = 'Vpg\\Disturb\\Core\\Storage\\ElasticsearchAdapter';
            break;
            default:
            throw new StorageException(
                'Adapter class not found',
                StorageException::CODE_ADAPTER
            );
        }

        if (! class_exists($adapterClass)) {
            throw new StorageException(
                'Adapter class not found : ' . $adapterClass,
                StorageException::CODE_ADAPTER
            );
        }

        // check if adapter config exists
        if (empty($config->getStorageConfig())) {
            throw new StorageException(
                'Adapter config not found',
                StorageException::CODE_ADAPTER
            );
        }

        $adapter = new $adapterClass();
        $adapter->initialize(new \Phalcon\Config($config->getStorageConfig()), $dbHash);
        return $adapter;
    }
}
