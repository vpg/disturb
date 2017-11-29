<?php
namespace Vpg\Disturb\Monitoring;

use \Phalcon\Config;
use \Phalcon\Mvc\User\Component;
use \Elasticsearch;

use Vpg\Disturb\Workflow;
use Vpg\Disturb\Core;
use Vpg\Disturb\Core\Storage;

/**
 * Class WorkerService monitoring
 *
 * @package  Disturb\Monitoring
 * @author   Jérôme Bourgeais <jbourgeais@voyageprive.com>
 * @license  https://github.com/vpg/disturb/blob/master/LICENSE MIT Licence
 */
class Service extends Component
{

    /**
     * ContextStorage constructor
     *
     * @param Json $config config
     *
     * @throws ContextStorageException
     */
    public function __construct(Config $config)
    {
        $this->di->get('logr')->debug(json_encode(func_get_args()));
        $this->config = new Workflow\WorkflowConfigDto($config);
        $this->initClient();
    }

    /**
     * Initialization of Elasticsearch Client
     *
     * @throws ContextStorageException
     *
     * @return void
     */
    private function initClient()
    {
        $this->di->get('logr')->debug(json_encode(func_get_args()));
        $storageHost = $this->config->getStorageHost();
        $this->storageClient = Storage\StorageAdapterFactory::get(
            $this->config,
            Storage\StorageAdapterFactory::USAGE_MONITORING
        );
    }

    /**
     * Registers a worker into the monitoring sys
     *
     * @param string $workerCode the worker's code to register 
     *
     * @return void
     */
    public function workerStarted(string $workerCode)
    {
        $this->di->get('logr')->debug(json_encode(func_get_args()));
        $workerHash = [
            'status' => Core\AbstractWorker::STATUS_STARTED,
            'runingOn' => php_uname("n"),
            'pid' => 1,
            'startedAt' => Date('Y-m-d H:i:s'),
            'heartBeatAt' => Date('Y-m-d H:i:s')
        ];
        $this->storageClient->save($workerCode, $workerHash);
    }

    /**
     * Registers a worker into the monitoring sys
     *
     * @param string $workerCode the worker's code to register 
     *
     * @return void
     */
    public function workerHeartBeats(string $workerCode)
    {
        $this->di->get('logr')->debug(json_encode(func_get_args()));
        $workerHash = [
            'heartBeatAt' => Date('Y-m-d H:i:s')
        ];
        $this->storageClient->save($workerCode, $workerHash);
    }

    /**
     * Registers a worker into the monitoring sys
     *
     * @param string $workerCode the worker's code to register 
     *
     * @return void
     */
    public function WorkerExited(string $workerCode, int $exitCode = 0)
    {
        $this->di->get('logr')->debug(json_encode(func_get_args()));
        $workerHash = [
            'exitedAt' => Date('Y-m-d H:i:s'),
            'runingOn' => php_uname("n"),
            'status' => Core\AbstractWorker::STATUS_EXITED,
            'exitCode' => $exitCode
        ];
        $this->storageClient->save($workerCode, $workerHash);
    }
}
