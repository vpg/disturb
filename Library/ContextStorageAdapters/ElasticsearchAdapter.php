<?php

namespace Vpg\Disturb\ContextStorageAdapters;

use Vpg\Disturb\Exceptions\ContextStorageException;

use \Phalcon\Config\Adapter\Json;

/**
 * Class Elasticsearch Adapter
 *
 * @package Vpg\Disturb\ContextStorageAdapters
 */
class ElasticsearchAdapter implements ContextStorageAdapterInterface
{
    /**
     * @const string VENDOR_CLASSNAME
     */
    const VENDOR_CLASSNAME = 'Elasticsearch';

    /**
     * @const string DEFAULT_INDEX
     */
    const DEFAULT_DOC_INDEX = 'disturb_context';

    /**
     * @const string DEFAULT_TYPE
     */
    const DEFAULT_DOC_TYPE = 'workflow';

    /**
     * @const string DOC_INDEX
     */
    const DOC_INDEX = 'index';

    /**
     * @const string DOC_TYPE
     */
    const DOC_TYPE = 'type';

    /**
     * @const string CONFIG_HOST
     */
    const CONFIG_HOST = 'host';

    /**
     * @const array REQUIRED_CONFIG_FIELD_LIST
     */
    const REQUIRED_CONFIG_FIELD_LIST = [
        self::CONFIG_HOST,
        self::DOC_INDEX,
        self::DOC_TYPE
    ];

    /*
     * @var Json $config
     */
    private $config;

    /**
     * @var \Elasticsearch\Client $client
     */
    private $client;

    /**
     * @var array $commonRequestParamHash
     */
    private $commonRequestParamHash = [];

    /**
     * Constructor
     */
    public function construct() {}

    /**
     * Initialize
     *
     * @param Json $config
     *
     * @return void
     */
    public function initialize(Json $config)
    {
        $this->checkVendorLibraryAvailable(self::VENDOR_CLASSNAME);
        $this->initConfig($config);
        $this->initClient();
    }

    /**
     * Init configuration
     *
     * @param Json $config
     *
     * @throws ContextStorageException
     */
    private function initConfig(Json $config)
    {
        if (empty($config)) {
            throw new ContextStorageException('Elasticsearch config not found');
        }

        // get default values for document index / type
        $config[self::DOC_INDEX] = self::DEFAULT_DOC_INDEX;
        $config[self::DOC_TYPE] = self::DEFAULT_DOC_TYPE;

        // check required config fields
        foreach (self::REQUIRED_CONFIG_FIELD_LIST as $configField) {
            if (empty($config[$configField])) {
                throw new ContextStorageException('Elasticsearch config ' . $configField . ' not found');
            }
            $this->config[$configField] = $config[$configField];
        }
    }

    /**
     * Init common request parameters
     *
     * @throws ContextStorageException
     */
    private function initCommonRequestParams()
    {
        foreach ([self::DOC_INDEX, self::DOC_TYPE] as $field) {
            $this->commonRequestParamHash[$field] = $this->config[$field];
        }
    }

    /**
     * Initialization of Elasticsearch Client
     *
     * @return void
     *
     * @throws ContextStorageException
     */
    private function initClient()
    {

        $this->client = \Elasticsearch\ClientBuilder::create()
            ->setHosts([$this->config[self::CONFIG_HOST]])
            ->build();

        // Check host connexion
        if (! $this->client->ping()) {
            throw new ContextStorageException('Elasticsearch host : ' . $this->config[self::CONFIG_HOST] . ' not available');
        }

        $this->initCommonRequestParams();

        // Check index
        // TODO: check if index exists ?
    }

    /**
     * Check if Elascticsearch dependencies library is available
     *
     * @param string $className
     *
     * @throws ContextStorageException
     */
    private function checkVendorLibraryAvailable($className)
    {
        if (!class_exists($className)) {
            throw new ContextStorageException($className . ' lib not found. Please make "composer update"');
        }
    }

    /**
     * Get document identified by id ($key)
     *
     * @param string $key
     *
     * @return array
     *
     * @throws ContextStorageException
     */
    public function get(string $key) : array
    {
        if (empty($key)) {
            throw new ContextStorageException('Elasticsearch get : invalid parameter');
        }

        $requestParamHash = array_merge(
            ['id' => $key],
            $this->commonRequestParamHash
        );

        return $this->client->get($requestParamHash);
    }

    /**
     * Check if a document identified by id ($key) exists
     *
     * @param string $key
     *
     * @return bool
     *
     * @throws ContextStorageException
     */
    public function exist(string $key) : bool
    {
        if (empty($key)) {
            throw new ContextStorageException('Elasticsearch exist : invalid parameter');
        }

        $requestParamHash = array_merge(
            ['id' => $key],
            $this->commonRequestParamHash
        );

        return $this->client->exists($requestParamHash);
    }

    /**
     * Save document  with id ($key)
     *
     * @param string $key
     * @param array $documentHash
     *
     * @return array
     *
     * @throws ContextStorageException
     */
    public function save(string $key, array $documentHash) : array
    {
        // Specify how many times should the operation be retried when a conflict occurs (simultaneous doc update)
        // TODO : check for param "retry_on_conflict"

        if (empty($key) || empty($documentHash)) {
            throw new ContextStorageException('Elasticsearch save invalid parameter');
        }

        $requestParamHash = array_merge(
            ['id' => $key],
            $this->commonRequestParamHash
        );

        if ($this->client->exists($requestParamHash)) {
            return $this->client->update(array_merge($requestParamHash, ['body' => ['doc' => $documentHash]]));
        } else {
            return $this->client->index(array_merge($requestParamHash, ['body' => $documentHash]));
        }
    }

    /**
     * Delete document identified by key
     *
     * @param string $key
     *
     * @return array
     *
     * @throws ContextStorageException
     */
    public function delete(string $key) : array
    {
        if (empty($key)) {
            throw new ContextStorageException('Elasticsearch delete invalid parameter');
        }

        $requestParamHash = array_merge(
            ['id' => $key],
            $this->commonRequestParamHash
        );

        return $this->client->delete($requestParamHash);
    }
}