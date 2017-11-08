<?php

namespace Vpg\Disturb\Services;

use Vpg\Disturb\Exceptions\ContextStorageException;
use Vpg\Disturb\ContextStorageAdapters\ContextStorageAdapterInterface;

use \Phalcon\Config\Adapter\Json;

class ContextStorage
{
    /**
     * @const string ADAPTER_ELASTICSEARCH
     */
    const ADAPTER_ELASTICSEARCH = 'elasticsearch';

    /**
     * @const string WORKFLOW
     */
    const WORKFLOW = 'workflow';

    /**
     * @const string WORKFLOW_STEPS
     */
    const WORKFLOW_STEPS = 'steps';

    /**
     * @const string WORKFLOW_STATUS
     */
    const WORKFLOW_STATUS = 'status';

    /**
     * @const string WORKFLOW_CURRENT_STEP_POS
     */
    const WORKFLOW_CURRENT_STEP_POS = 'currentStepPos';

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
            throw new ContextStorageException(
                'Adapter name not found',
                ContextStorageException::CODE_ADAPTER
            );
        }

        // check if adapter class exists
        switch ($config->adapter) {
            case self::ADAPTER_ELASTICSEARCH:
                $adapterClass = 'ElasticsearchAdapter';
                break;
            default:
                throw new ContextStorageException(
                    'Adapter class not found',
                    ContextStorageException::CODE_ADAPTER
                );
        }

        if (! class_exists($adapterClass)) {
            throw new ContextStorageException(
                'Adapter class not found',
                ContextStorageException::CODE_ADAPTER
            );
        }

        // check if adapter config exists
        if (empty($config->config)) {
            throw new ContextStorageException(
                'Adapter config not found',
                ContextStorageException::CODE_ADAPTER
            );
        }

        $this->adpater = new $adapterClass();
        $this->adpater->initialize($config->config);
    }

    /**
     * Get storage data identified by $workflowProcessId
     *
     * @param string $workflowProcessId
     *
     * @return mixed
     */
    public function get(string $workflowProcessId) {
        return $this->adpater->get($workflowProcessId);
    }

    /**
     * Check if storage date $workflowProcessId exists
     *
     * @param string $workflowProcessId
     *
     * @return bool
     */
    public function exist(string $workflowProcessId) {
        return $this->adpater->exist($workflowProcessId);
    }

    /**
     * Save storage data identified by $workflowProcessId
     *
     * @param string $workflowProcessId
     * @param array $valueHash
     *
     * @return mixed
     */
    public function save(string $workflowProcessId, array $valueHash) {
        return $this->adpater->save($workflowProcessId, $valueHash);
    }

    /**
     * Delete storage data identified by $workflowProcessId
     *
     * @param string $workflowProcessId
     *
     * @returns mixed
     */
    public function delete(string $workflowProcessId) {
        return $this->adpater->delete($workflowProcessId);
    }

    /**
     * Set workflow status
     *
     * @param string $workflowProcessId
     * @param string $status
     */
    public function setWorkflowStatus(string $workflowProcessId, string $status) {
        $contextHash = $this->get($workflowProcessId);
        $contextHash[self::WORKFLOW_STATUS] = $status;
        $this->save($workflowProcessId, $contextHash);

    }

    /**
     * Get workflow status
     *
     * @param string $workflowProcessId
     *
     * @returns string
     */
    public function getWorkflowStatus(string $workflowProcessId) : string {
        $contextHash = $this->get($workflowProcessId);
        return isset($contextHash[self::WORKFLOW_STATUS]) ?
            $contextHash[self::WORKFLOW_STATUS] : \Disturb\Services\WorkflowManager::STATUS_NO_STARTED;
    }

    /**
     * Init workflow next step
     *
     * @param string $workflowProcessId
     */
    public function initWorkflowNextStep(string $workflowProcessId) {
        $contextHash = $this->get($workflowProcessId);
        $contextHash[self::WORKFLOW_CURRENT_STEP_POS]++;
        $this->save($workflowProcessId, $contextHash);
    }

    /**
     * Get workflow next step position
     *
     * @param string $workflowProcessId
     *
     * @returns int
     */
    public function getWorkflowNextStepPosition(string $workflowProcessId) : int {
        $contextHash = $this->get($workflowProcessId);
        return $contextHash[self::WORKFLOW_CURRENT_STEP_POS] + 1;
    }

    /**
     * Get workflow current step position
     *
     * @param string $workflowProcessId
     *
     * @returns int
     */
    public function getWorkflowCurrentStepPosition(string $workflowProcessId) : int {
        $contextHash = $this->get($workflowProcessId);
        return $contextHash[self::WORKFLOW_CURRENT_STEP_POS];
    }

    /**
     * Get workflow current step list
     *
     * @param string $workflowProcessId
     * @param int $currentWorkflowPosition
     *
     * @returns array
     */
    public function getWorkflowCurrentStepList(string $workflowProcessId, int $currentWorkflowPosition) : array {
        $contextHash = $this->get($workflowProcessId);
        return $contextHash[self::WORKFLOW][self::WORKFLOW_STEPS][$currentWorkflowPosition];
    }

    /**
     * Get workflow step list
     *
     * @param string $workflowProcessId
     *
     * @returns array
     */
    public function getWorkflowStepList(string $workflowProcessId) : array {
        $contextHash = $this->get($workflowProcessId);
        return $contextHash[self::WORKFLOW][self::WORKFLOW_STEPS];
    }

    /**
     * Set workflow step list
     *
     * @param string $workflowProcessId
     * @param array $workflowStepList
     *
     * @returns array
     */
    public function setWorkflowStepList(string $workflowProcessId, $workflowStepList) {
        $contextHash = $this->get($workflowProcessId);
        $contextHash[self::WORKFLOW][self::WORKFLOW_STEPS] = $workflowStepList;
        $this->save($workflowProcessId, $contextHash);
    }


}