<?php
/**
 * User: adefretin
 * Date: 22/09/2017
 * Time: 12:17
 */

namespace Disturb\ContextStorage;

/**
 * Class Elasticsearch
 *
 * @package Disturb\ContextStorage
 */
class Elasticsearch implements ContextStorageInterface
{
    /**
     * @const string DOC_INDEX
     */
    const DOC_INDEX = 'index';

    /**
     * @const string DOC_TYPE
     */
    const DOC_TYPE = 'type';

    /*
     * @var \Phalcon\Config\Adapter\Json $config
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
    public function construct() {
        $this->checkVendorLibraryAvailable();
        $this->initConfig();
        $this->initClient();
    }

    /**
     * Init configuration
     *
     * @throws \Exception
     */
    private function initConfig() {
        if (!file_exists(realpath('./../Config/ContextStorage/elasticsearch.json'))) {
            throw new \Exception('Elasticsearch config not found');
        }
        $this->config = new \Phalcon\Config\Adapter\Json(realpath('./../Config/ContextStorage/elasticsearch.json'));
    }

    /**
     * Init common request parameters
     *
     * @throws \Exception
     */
    private function initCommonRequestParams () {
        foreach ([self::DOC_INDEX, self::DOC_TYPE] as $field) {
            if (empty($this->config->context->$field)) {
                throw new \Exception('Elasticsearch config not found [ context : ' . $field . ' ]');
            }
        }
        $this->commonRequestParamHash = [
            self::DOC_INDEX => $this->config->context->index,
            self::DOC_TYPE => $this->config->context->type
        ];
    }

    /**
     * Initialization of Elasticsearch Client
     *
     * @throws \Exception
     */
    private function initClient() {
        $hostConfigHash = [
            'host' => $this->config->host,
            'port' => $this->config->port,
            'scheme' => $this->config->scheme
        ];

        $this->client = \Elasticsearch\ClientBuilder::create()
            ->setHosts([$hostConfigHash])
            ->build();

        $this->initCommonRequestParams();

        // Check connexion
        if (! $this->client->ping($this->commonRequestParamHash)) {
            throw new \Exception('Elasticsearch index/type not available');
        }
    }

    /**
     * Check if Elascticsearch dependencies librairy is available
     *
     * @throws \Exception
     */
    private function checkVendorLibraryAvailable() {
        if (!file_exists(realpath('./../vendor/elasticsearch/elasticsearch/README.md'))) {
            throw new \Exception('Elasticsearch lib not found. Please make "composer update"');
        }
    }

    /**
     * Get document identified by id ($key)
     *
     * @param string $key
     *
     * @return array
     *
     * @throws \Exception
     */
    public function get(string $key) : array {

        if (empty($key)) {
            throw new \Exception('Elasticsearch get invalid parameter');
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
     * @throws \Exception
     */
    public function exist(string $key) : bool {
        if (empty($key)) {
            throw new \Exception('Elasticsearch exist invalid parameter');
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
     * @throws \Exception
     */
    public function save(string $key, array $documentHash) : array {

        // Specify how many times should the operation be retried when a conflict occurs (simultaneous doc update)
        // TODO : check for param "retry_on_conflict"

        if (empty($key) || empty($documentHash)) {
            throw new \Exception('Elasticsearch save invalid parameter');
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
     * @throws \Exception
     */
    public function delete(string $key) : array {
        if (empty($key)) {
            throw new \Exception('Elasticsearch delete invalid parameter');
        }

        $requestParamHash = array_merge(
            ['id' => $key],
            $this->commonRequestParamHash
        );

        return $this->client->delete($requestParamHash);
    }
}