<?php

namespace Vpg\Disturb\Services;

use Vpg\Disturb\Exceptions\ContextStorageException;
use Vpg\Disturb\ContextStorageAdapters\ContextStorageAdapterInterface;
use Vpg\Disturb\ContextStorageAdapters\ElasticsearchAdapter;

use \Phalcon\Config;
use \Phalcon\Mvc\User\Component;

class ContextStorage extends Component
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
    private $adapter;

    /**
     * ContextStorage constructor
     *
     * @param Json $config
     *
     * @throws ContextStorageException
     */
    public function __construct(Config $config)
    {
        $this->di->get('logger')->debug(json_encode(func_get_args()));
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
                $adapterClass = 'Vpg\\Disturb\\ContextStorageAdapters\\ElasticsearchAdapter';
                break;
            default:
                throw new ContextStorageException(
                    'Adapter class not found',
                    ContextStorageException::CODE_ADAPTER
                );
        }

        if (! class_exists($adapterClass)) {
            throw new ContextStorageException(
                'Adapter class not found : ' . $adapterClass,
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

        $this->adapter = new $adapterClass();
        $this->adapter->initialize($config->config);
    }

    /**
     * Get storage data identified by $workflowProcessId
     *
     * @param string $workflowProcessId
     *
     * @return mixed
     */
    public function get(string $workflowProcessId) {
        $this->di->get('logger')->debug(json_encode(func_get_args()));
        return $this->adapter->get($workflowProcessId);
    }

    /**
     * Check if storage date $workflowProcessId exists
     *
     * @param string $workflowProcessId
     *
     * @return bool
     */
    public function exist(string $workflowProcessId) {
        $this->di->get('logger')->debug(json_encode(func_get_args()));
        return $this->adapter->exist($workflowProcessId);
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
        $this->di->get('logger')->debug(json_encode(func_get_args()));
        return $this->adapter->save($workflowProcessId, $valueHash);
    }

    /**
     * Delete storage data identified by $workflowProcessId
     *
     * @param string $workflowProcessId
     *
     * @returns mixed
     */
    public function delete(string $workflowProcessId) {
        $this->di->get('logger')->debug(json_encode(func_get_args()));
        return $this->adapter->delete($workflowProcessId);
    }

    /**
     * Set workflow status
     *
     * @param string $workflowProcessId
     * @param string $status
     */
    public function setWorkflowStatus(string $workflowProcessId, string $status) {
        $this->di->get('logger')->debug(json_encode(func_get_args()));
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
        $this->di->get('logger')->debug(json_encode(func_get_args()));
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
        $this->di->get('logger')->debug(json_encode(func_get_args()));
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
        $this->di->get('logger')->debug(json_encode(func_get_args()));
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
        $this->di->get('logger')->debug(json_encode(func_get_args()));
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
        $this->di->get('logger')->debug(json_encode(func_get_args()));
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
        $this->di->get('logger')->debug(json_encode(func_get_args()));
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
        $this->di->get('logger')->debug(json_encode(func_get_args()));
        $contextHash = $this->get($workflowProcessId);
        $contextHash[self::WORKFLOW][self::WORKFLOW_STEPS] = $workflowStepList;
        $this->save($workflowProcessId, $contextHash);
    }

    /**
     * Update the given step related to the given workflow/stepcode
     *
     * @param string $workflowProcessId the workflow identifier
     * @param string $stepCode          the step code
     * @param array  $stepHash          the step data to save
     *
     * @returns array
     */
    public function updateWorkflowStep(string $workflowProcessId, $stepCode, $stepHash)
    {
        $this->di->get('logger')->debug(json_encode(func_get_args()));
        $script = <<<EOT
        def nbi=ctx._source.workflow.steps.size();
        for (i=0; i < nbi; i++) {
            if (ctx._source.workflow.steps[i] instanceof List) {
                def nbj=ctx._source.workflow.steps[i].size();
                for (j=0; j < nbj; j++) {
                    if (ctx._source.workflow.steps[i][j].name == stepCode) {
                        ctx._source.workflow.steps[i][j] = stepHash;
                        break;
                    }
                }
            } else if (ctx._source.workflow.steps[i].name == stepCode) {
                ctx._source.workflow.steps[i] =stepHash;
            }
        }
EOT;
        $updateHash = [
            'script' => [
                'lang' => 'groovy',
                'inline' => $script,
                'params' => [
                    'stepCode' => $stepCode,
                    'stepHash' => $stepHash
                ]
            ]
        ];
        return $this->adapter->update($workflowProcessId, $updateHash);
    }


}
