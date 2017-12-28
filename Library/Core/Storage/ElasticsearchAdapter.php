<?php
namespace Vpg\Disturb\Core\Storage;

use \Phalcon\Mvc\User\Component;
use \Phalcon\Config;
use \Elasticsearch;

/**
 * Class ElasticsearchAdapter
 *
 * @package  Disturb\Core\Storage
 * @author   Alexandre DEFRETIN <adefretin@voyageprive.com>
 * @license  https://github.com/vpg/disturb/blob/master/LICENSE MIT Licence
 */
class ElasticsearchAdapter extends Component implements StorageAdapterInterface
{
    /**
     * Vendor class name const
     *
     * @const string VENDOR_CLASSNAME
     */
    const VENDOR_CLASSNAME = '\\Elasticsearch\\Client';

    /**
     * Doc source const
     *
     * @const string DEFAULT_DOC_SOURCE
     */
    const DEFAULT_DOC_SOURCE = '_source';

    /**
     * Doc index const
     *
     * @const string DOC_INDEX
     */
    const DOC_INDEX = 'index';

    /**
     * Doc type const
     *
     * @const string DOC_TYPE
     */
    const DOC_TYPE = 'type';

    /**
     * Config host const
     *
     * @const string CONFIG_HOST
     */
    const CONFIG_HOST = 'host';

    /**
     * Context usage config
     *
     * @const string USAGE_CONTEXT_CONFIG
     */
    const USAGE_CONTEXT_CONFIG = ['index' => 'disturb_context', 'type' => 'workflow'];

    /**
     * Monitoring usage config
     *
     * @const string USAGE_MONITORING_CONFIG
     */
    const USAGE_MONITORING_CONFIG = ['index' => 'disturb_monitoring', 'type' => 'worker'];

    /**
     * Required config field list const
     *
     * @const array REQUIRED_CONFIG_FIELD_LIST
     */
    const REQUIRED_CONFIG_FIELD_LIST = [
        self::CONFIG_HOST,
        self::DOC_INDEX,
        self::DOC_TYPE
    ];

    /**
     * Config JSON
     *
     * @var Json $_config
     */
    private $config;

    /**
     * Elasticsearch client
     *
     * @var \Elasticsearch\Client $client client
     */
    private $client;

    /**
     * Common Request params
     *
     * @var array $_commonRequestParamHash commonRequestParamHash
     */
    private $commonRequestParamHash = [];

    /**
     * Constructor
     *
     * @return void
     */
    public function construct()
    {
    }

    /**
     * Initialize
     *
     * @param Json   $config config
     * @param string $usage  define the usage, could either be context or monitoring
     *
     * @return void
     */
    public function initialize(Config $config, string $usage)
    {
        $this->di->get('logr')->debug(json_encode(func_get_args()));
        $this->checkVendorLibraryAvailable(self::VENDOR_CLASSNAME);
        switch ($usage) {
            case StorageAdapterFactory::USAGE_CONTEXT:
                $dbHash = self::USAGE_CONTEXT_CONFIG;
            break;
            case StorageAdapterFactory::USAGE_MONITORING:
                $dbHash = self::USAGE_MONITORING_CONFIG;
            break;
            default:
                throw new StorageException(
                    "Unkown usage : $usage",
                    StorageException::CODE_INVALID_PARAMETER
                );

        }
        $this->initConfig($config, $dbHash);
        $this->initClient();
    }

    /**
     * Check if Elascticsearch dependencies library is available
     *
     * @param string $className className
     *
     * @throws StorageException
     * @return void
     */
    private function checkVendorLibraryAvailable($className)
    {
        if (!class_exists($className)) {
            throw new StorageException(
                $className . ' lib not found. Please make "composer update"',
                StorageException::CODE_VENDOR
            );
        }
    }

    /**
     * Check parameters
     *
     * @param array $parametersList parametersList
     *
     * @throws StorageException
     * @return void
     */
    private function checkParameters(array $parametersList)
    {
        foreach ($parametersList as $parameter) {
            if (empty($parameter)) {
                throw new StorageException(
                    'invalid parameter',
                    StorageException::CODE_INVALID_PARAMETER,
                    null,
                    2
                );
            }
        }
    }

    /**
     * Init configuration
     *
     * @param Json  $config host config
     * @param array $dbHash index and type config
     *
     * @throws StorageException
     * @return void
     */
    private function initConfig(Config $config, array $dbHash)
    {
        $this->checkParameters([$config]);

        // get default values for document index / type
        $config[self::DOC_INDEX] = $dbHash[self::DOC_INDEX];
        $config[self::DOC_TYPE] = $dbHash[self::DOC_TYPE];

        // check required config fields
        foreach (self::REQUIRED_CONFIG_FIELD_LIST as $configField) {
            if (empty($config[$configField])) {
                throw new StorageException(
                    'config ' . $configField . ' not found',
                    StorageException::CODE_CONFIG
                );
            }
            $this->config[$configField] = $config[$configField];
        }
    }

    /**
     * Init common request parameters
     *
     * @return void
     *
     * @throws StorageException
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
     * @throws StorageException
     */
    private function initClient()
    {
        $this->di->get('logr')->debug(json_encode($this->config[self::CONFIG_HOST]));
        $this->client = \Elasticsearch\ClientBuilder::create()
            ->setHosts([$this->config[self::CONFIG_HOST]])
            ->build();

        $this->getDI()->get('logr')->info("Connecting to Elastic " . json_encode($this->config[self::CONFIG_HOST]));
        // Check host connexion
        if (! $this->client->ping()) {
            throw new StorageException('host : ' . $this->config[self::CONFIG_HOST] . ' not available');
        }

        $this->initCommonRequestParams();

        // Check index
        // TODO: check if index exists ?
    }

    /**
     * Get document identified by id ($id)
     *
     * @param string $id id
     *
     * @return array
     *
     * @throws StorageException
     */
    public function get(string $id) : array
    {
        $this->di->get('logr')->debug(json_encode(func_get_args()));
        $this->checkParameters([$id]);

        try {
            $requestParamHash = array_merge(
                ['id' => $id],
                $this->commonRequestParamHash
            );
            $resultHash = $this->client->get($requestParamHash);
            return $resultHash[self::DEFAULT_DOC_SOURCE];
        } catch (\Exception $exception) {
            throw new StorageException(
                'document not found',
                StorageException::CODE_GET,
                $exception
            );
        }
    }

    /**
     * Search document by query $queryParameterHash
     *
     * @param array $queryParameterHash queryParameterHash
     *
     * @return array
     */
    public function search(array $queryParameterHash) : array
    {
        $this->di->get('logr')->debug(json_encode(func_get_args()));
        // TODO
        return [];
    }

    /**
     * Check if a document identified by id ($id) exists
     *
     * @param string $id id
     *
     * @return bool
     *
     * @throws StorageException
     */
    public function exists(string $id) : bool
    {
        $this->di->get('logr')->debug(json_encode(func_get_args()));
        $this->checkParameters([$id]);

        try {
            $requestParamHash = array_merge(
                ['id' => $id],
                $this->commonRequestParamHash
            );
            return $this->client->exists($requestParamHash);
        } catch (\Exception $exception) {
            throw new StorageException(
                'can not check if docuement exist',
                StorageException::CODE_EXIST,
                $exception
            );
        }
    }

    /**
     * Save document  with id ($id)
     *
     * @param string $id           id
     * @param array  $documentHash document hash
     *
     * @return array
     *
     * @throws StorageException
     */
    public function save(string $id, array $documentHash) : array
    {
        $this->di->get('logr')->debug(json_encode(func_get_args()));
        // Specify how many times should the operation be retried when a conflict occurs (simultaneous doc update)
        // TODO : check for param "retry_on_conflict"

        $this->checkParameters([$id, $documentHash]);

        try {
            $requestParamHash = array_merge(
                ['id' => $id],
                $this->commonRequestParamHash
            );

            if ($this->client->exists($requestParamHash)) {
                return $this->client->update(array_merge($requestParamHash, ['body' => ['doc' => $documentHash]]));
            } else {
                return $this->client->index(array_merge($requestParamHash, ['body' => $documentHash]));
            }
        } catch (\Exception $exception) {
            throw new StorageException(
                'Fail to save document',
                StorageException::CODE_SAVE,
                $exception
            );
        }
    }

    /**
     * Delete document identified by $id
     *
     * @param string $id id
     *
     * @return array
     *
     * @throws StorageException
     */
    public function delete(string $id) : array
    {
        $this->di->get('logr')->debug(json_encode(func_get_args()));
        $this->checkParameters([$id]);

        try {
            $requestParamHash = array_merge(
                ['id' => $id],
                $this->commonRequestParamHash
            );
            return $this->client->delete($requestParamHash);
        } catch (\Exception $exception) {
            throw new StorageException(
                'Fail to delete document',
                StorageException::CODE_DELETE,
                $exception
            );
        }
    }

    /**
     * Updates the document with id ($id)
     *
     * @param string $id         id
     * @param array  $updateHash document hash
     * @param int    $retryNb    The numer of time the update will be retried in case of conflict
     *
     * @return array
     *
     * @throws StorageException
     */
    public function update(string $id, array $updateHash, int $retryNb = 0) : array
    {
        $this->di->get('logr')->debug(json_encode(func_get_args()));
        $this->checkParameters([$id, $updateHash]);

        try {
            $requestParamHash = array_merge(
                ['id' => $id],
                $this->commonRequestParamHash
            );
            if ($retryNb) {
                $requestParamHash['retry_on_conflict'] = $retryNb;
            }
            return $this->client->update(array_merge($requestParamHash, ['body' => $updateHash]));
        } catch (\Exception $exception) {
            throw new StorageException(
                'Fail to update document : ' . $exception->getMessage(),
                StorageException::CODE_SAVE,
                $exception
            );
        }
    }
}
