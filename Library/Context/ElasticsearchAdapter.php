<?php
namespace Vpg\Disturb\Context;

use \Phalcon\Mvc\User\Component;
use \Phalcon\Config;
use \Elasticsearch;

/**
 * Class ElasticsearchAdapter
 *
 * @package  Disturb\Context
 * @author   Alexandre DEFRETIN <adefretin@voyageprive.com>
 * @license  https://github.com/vpg/disturb/blob/master/LICENSE MIT Licence
 */
class ElasticsearchAdapter extends Component implements ContextStorageAdapterInterface
{
    /**
     * Vendor class name const
     *
     * @const string VENDOR_CLASSNAME
     */
    const VENDOR_CLASSNAME = '\\Elasticsearch\\Client';

    /**
     * Default doc index const
     *
     * @const string DEFAULT_INDEX
     */
    const DEFAULT_DOC_INDEX = 'disturb_context';

    /**
     * Default type const
     *
     * @const string DEFAULT_TYPE
     */
    const DEFAULT_DOC_TYPE = 'workflow';

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
     * @param Json $config config
     *
     * @return void
     */
    public function initialize(Config $config)
    {
        $this->di->get('logger')->debug(json_encode(func_get_args()));
        $this->checkVendorLibraryAvailable(self::VENDOR_CLASSNAME);
        $this->initConfig($config);
        $this->initClient();
    }

    /**
     * Check if Elascticsearch dependencies library is available
     *
     * @param string $className className
     *
     * @throws ContextStorageException
     * @return void
     */
    private function checkVendorLibraryAvailable($className)
    {
        if (!class_exists($className)) {
            throw new ContextStorageException(
                $className . ' lib not found. Please make "composer update"',
                ContextStorageException::CODE_VENDOR
            );
        }
    }

    /**
     * Check parameters
     *
     * @param array $parametersList parametersList
     *
     * @throws ContextStorageException
     * @return void
     */
    private function checkParameters(array $parametersList)
    {
        foreach ($parametersList as $parameter) {
            if (empty($parameter)) {
                throw new ContextStorageException(
                    'invalid parameter',
                    ContextStorageException::CODE_INVALID_PARAMETER,
                    null,
                    2
                );
            }
        }
    }

    /**
     * Init configuration
     *
     * @param Json $config config
     *
     * @throws ContextStorageException
     * @return void
     */
    private function initConfig(Config $config)
    {
        $this->checkParameters([$config]);

        // get default values for document index / type
        $config[self::DOC_INDEX] = self::DEFAULT_DOC_INDEX;
        $config[self::DOC_TYPE] = self::DEFAULT_DOC_TYPE;

        // check required config fields
        foreach (self::REQUIRED_CONFIG_FIELD_LIST as $configField) {
            if (empty($config[$configField])) {
                throw new ContextStorageException(
                    'config ' . $configField . ' not found',
                    ContextStorageException::CODE_CONFIG
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
        $this->di->get('logger')->debug(json_encode($this->config[self::CONFIG_HOST]));
        $this->client = \Elasticsearch\ClientBuilder::create()
            ->setHosts([$this->config[self::CONFIG_HOST]])
            ->build();

        // Check host connexion
        if (! $this->client->ping()) {
            throw new ContextStorageException('host : ' . $this->config[self::CONFIG_HOST] . ' not available');
        }

        $this->initCommonRequestParams();

        // Check index
        // TODO: check if index exists ?
    }

    /**
     * Get document identified by id ($workflowProcessId)
     *
     * @param string $workflowProcessId workflowProcessId
     *
     * @return array
     *
     * @throws ContextStorageException
     */
    public function get(string $workflowProcessId) : array
    {
        $this->di->get('logger')->debug(json_encode(func_get_args()));
        $this->checkParameters([$workflowProcessId]);

        try {
            $requestParamHash = array_merge(
                ['id' => $workflowProcessId],
                $this->commonRequestParamHash
            );
            $resultHash = $this->client->get($requestParamHash);
            return $resultHash[self::DEFAULT_DOC_SOURCE];
        } catch (\Exception $exception) {
            throw new ContextStorageException(
                'document not found',
                ContextStorageException::CODE_GET,
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
        $this->di->get('logger')->debug(json_encode(func_get_args()));
        // TODO
        return [];
    }

    /**
     * Check if a document identified by id ($workflowProcessId) exists
     *
     * @param string $workflowProcessId workflowProcessId
     *
     * @return bool
     *
     * @throws ContextStorageException
     */
    public function exist(string $workflowProcessId) : bool
    {
        $this->di->get('logger')->debug(json_encode(func_get_args()));
        $this->checkParameters([$workflowProcessId]);

        try {
            $requestParamHash = array_merge(
                ['id' => $workflowProcessId],
                $this->commonRequestParamHash
            );
            return $this->client->exists($requestParamHash);
        } catch (\Exception $exception) {
            throw new ContextStorageException(
                'can not check if docuement exist',
                ContextStorageException::CODE_EXIST,
                $exception
            );
        }
    }

    /**
     * Save document  with id ($workflowProcessId)
     *
     * @param string $workflowProcessId workflowProcessId
     * @param array  $documentHash      document hash
     *
     * @return array
     *
     * @throws ContextStorageException
     */
    public function save(string $workflowProcessId, array $documentHash) : array
    {
        $this->di->get('logger')->debug(json_encode(func_get_args()));
        // Specify how many times should the operation be retried when a conflict occurs (simultaneous doc update)
        // TODO : check for param "retry_on_conflict"

        $this->checkParameters([$workflowProcessId, $documentHash]);

        try {
            $requestParamHash = array_merge(
                ['id' => $workflowProcessId],
                $this->commonRequestParamHash
            );

            if ($this->client->exists($requestParamHash)) {
                return $this->client->update(array_merge($requestParamHash, ['body' => ['doc' => $documentHash]]));
            } else {
                return $this->client->index(array_merge($requestParamHash, ['body' => $documentHash]));
            }
        } catch (\Exception $exception) {
            throw new ContextStorageException(
                'Fail to save document',
                ContextStorageException::CODE_SAVE,
                $exception
            );
        }
    }

    /**
     * Delete document identified by $workflowProcessId
     *
     * @param string $workflowProcessId workflowProcessId
     *
     * @return array
     *
     * @throws ContextStorageException
     */
    public function delete(string $workflowProcessId) : array
    {
        $this->di->get('logger')->debug(json_encode(func_get_args()));
        $this->checkParameters([$workflowProcessId]);

        try {
            $requestParamHash = array_merge(
                ['id' => $workflowProcessId],
                $this->commonRequestParamHash
            );
            return $this->client->delete($requestParamHash);
        } catch (\Exception $exception) {
            throw new ContextStorageException(
                'Fail to delete document',
                ContextStorageException::CODE_DELETE,
                $exception
            );
        }
    }

    /**
     * Updates the document with id ($workflowProcessId)
     *
     * @param string $workflowProcessId workflowProcessId
     * @param array  $updateHash        document hash
     *
     * @return array
     *
     * @throws ContextStorageException
     */
    public function update(string $workflowProcessId, array $updateHash) : array
    {
        $this->di->get('logger')->debug(json_encode(func_get_args()));
        $this->checkParameters([$workflowProcessId, $updateHash]);

        try {
            $requestParamHash = array_merge(
                ['id' => $workflowProcessId],
                $this->commonRequestParamHash
            );
            return $this->client->update(array_merge($requestParamHash, ['body' => $updateHash]));
        } catch (\Exception $exception) {
            echo $exception->getMessage();
            throw new ContextStorageException(
                'Fail to update document',
                ContextStorageException::CODE_SAVE,
                $exception
            );
        }
    }
}
