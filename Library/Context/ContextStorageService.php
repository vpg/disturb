<?php
namespace Vpg\Disturb\Context;

use \Phalcon\Config;
use \Phalcon\Mvc\User\Component;

use Vpg\Disturb\Workflow;

/**
 * Class ContextStorage
 *
 * @category Services
 * @package  Disturb\Context
 * @author   Alexandre DEFRETIN <adefretin@voyageprive.com>
 * @license  https://github.com/vpg/disturb/blob/master/LICENSE MIT Licence
 */
class ContextStorageService extends Component
{
    /**
     * Elastic search adapter
     *
     * @const string ADAPTER_ELASTICSEARCH
     */
    const ADAPTER_ELASTICSEARCH = 'elasticsearch';

    /**
     * Workflow
     *
     * @const string WORKFLOW
     */
    const WORKFLOW = 'workflow';

    /**
     * Workflow steps
     *
     * @const string WORKFLOW_STEPS
     */
    const WORKFLOW_STEPS = 'steps';

    /**
     * Workflow status
     *
     * @const string WORKFLOW_STATUS
     */
    const WORKFLOW_STATUS = 'status';

    /**
     * Workflow current step pos
     *
     * @const string WORKFLOW_CURRENT_STEP_POS
     */
    const WORKFLOW_CURRENT_STEP_POS = 'currentStepPos';

    /**
     * Adapter
     *
     * @var ContextStorageAdapterInterface $adapter
     */
    private $adapter;

    /**
     * ContextStorage constructor
     *
     * @param Json $config config
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
                $adapterClass = 'Vpg\\Disturb\\Context\\ElasticsearchAdapter';
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
     * @param string $workflowProcessId the workflow process id
     *
     * @return mixed
     */
    public function get(string $workflowProcessId)
    {
        $this->di->get('logger')->debug(json_encode(func_get_args()));
        return $this->adapter->get($workflowProcessId);
    }

    /**
     * Check if storage date $workflowProcessId exists
     *
     * @param string $workflowProcessId the workflow process id
     *
     * @return bool
     */
    public function exist(string $workflowProcessId)
    {
        $this->di->get('logger')->debug(json_encode(func_get_args()));
        return $this->adapter->exist($workflowProcessId);
    }

    /**
     * Save storage data identified by $workflowProcessId
     *
     * @param string $workflowProcessId the workflow process id
     * @param array  $valueHash         value hash
     *
     * @return mixed
     */
    public function save(string $workflowProcessId, array $valueHash)
    {
        $this->di->get('logger')->debug(json_encode(func_get_args()));
        return $this->adapter->save($workflowProcessId, $valueHash);
    }

    /**
     * Delete storage data identified by $workflowProcessId
     *
     * @param string $workflowProcessId the workflow process id
     *
     * @return mixed
     */
    public function delete(string $workflowProcessId)
    {
        $this->di->get('logger')->debug(json_encode(func_get_args()));
        return $this->adapter->delete($workflowProcessId);
    }

    /**
     * Set workflow status
     *
     * @param string $workflowProcessId the workflow process id
     * @param string $status            status
     *
     * @return void
     */
    public function setWorkflowStatus(string $workflowProcessId, string $status)
    {
        $this->di->get('logger')->debug(json_encode(func_get_args()));
        $contextHash = $this->get($workflowProcessId);
        $contextHash[self::WORKFLOW_STATUS] = $status;
        $this->save($workflowProcessId, $contextHash);

    }

    /**
     * Get workflow status
     *
     * @param string $workflowProcessId the workflow process id
     *
     * @return string
     */
    public function getWorkflowStatus(string $workflowProcessId) : string
    {
        $this->di->get('logger')->debug(json_encode(func_get_args()));
        $contextHash = $this->get($workflowProcessId);
        if (isset($contextHash[self::WORKFLOW_STATUS])) {
            return $contextHash[self::WORKFLOW_STATUS];
        } else {
            return Workflow\ManagerService::STATUS_NO_STARTED;
        }
    }

    /**
     * Init workflow next step
     *
     * @param string $workflowProcessId the workflow process id
     *
     * @return void
     */
    public function initWorkflowNextStep(string $workflowProcessId)
    {
        $this->di->get('logger')->debug(json_encode(func_get_args()));
        $contextHash = $this->get($workflowProcessId);
        $contextHash[self::WORKFLOW_CURRENT_STEP_POS]++;
        $this->save($workflowProcessId, $contextHash);
    }

    /**
     * Get workflow next step position
     *
     * @param string $workflowProcessId the workflow process id
     *
     * @return int
     */
    public function getWorkflowNextStepPosition(string $workflowProcessId) : int
    {
        $this->di->get('logger')->debug(json_encode(func_get_args()));
        $contextHash = $this->get($workflowProcessId);
        return $contextHash[self::WORKFLOW_CURRENT_STEP_POS] + 1;
    }

    /**
     * Get workflow current step position
     *
     * @param string $workflowProcessId the workflow process id
     *
     * @return int
     */
    public function getWorkflowCurrentStepPosition(string $workflowProcessId) : int
    {
        $this->di->get('logger')->debug(json_encode(func_get_args()));
        $contextHash = $this->get($workflowProcessId);
        return $contextHash[self::WORKFLOW_CURRENT_STEP_POS];
    }

    /**
     * Get workflow current step list
     *
     * @param string $workflowProcessId       the workflow process id
     * @param int    $currentWorkflowPosition currentWorkflowPosition
     *
     * @return array
     */
    public function getWorkflowCurrentStepList(string $workflowProcessId, int $currentWorkflowPosition) : array
    {
        $this->di->get('logger')->debug(json_encode(func_get_args()));
        $contextHash = $this->get($workflowProcessId);
        return $contextHash[self::WORKFLOW][self::WORKFLOW_STEPS][$currentWorkflowPosition];
    }

    /**
     * Get workflow step list
     *
     * @param string $workflowProcessId the workflow process id
     *
     * @return array
     */
    public function getWorkflowStepList(string $workflowProcessId) : array
    {
        $this->di->get('logger')->debug(json_encode(func_get_args()));
        $contextHash = $this->get($workflowProcessId);
        return $contextHash[self::WORKFLOW][self::WORKFLOW_STEPS];
    }

    /**
     * Set workflow step list
     *
     * @param string $workflowProcessId the workflow process id
     * @param array  $workflowStepList  the workflow step list
     *
     * @return array
     */
    public function setWorkflowStepList(string $workflowProcessId, array $workflowStepList)
    {
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
     * @return array
     */
    public function updateWorkflowStep(string $workflowProcessId, string $stepCode, array $stepHash)
    {
        $this->di->get('logger')->debug(json_encode(func_get_args()));
        $script = <<<EOT
        def nbStep = ctx._source.workflow.steps.size();
        for (stepIndex = 0; stepIndex < nbStep; stepIndex++) {
            if (ctx._source.workflow.steps[stepIndex] instanceof List) {
                def nbParallelizedStep = ctx._source.workflow.steps[stepIndex].size();
                for (parallelizedStepIndex = 0; parallelizedStepIndex < nbParallelizedStep; parallelizedStepIndex++) {
                    if (ctx._source.workflow.steps[stepIndex][parallelizedStepIndex].name == stepCode) {
                        ctx._source.workflow.steps[stepIndex][parallelizedStepIndex] = stepHash;
                        break;
                    }
                }
            } else if (ctx._source.workflow.steps[stepIndex].name == stepCode) {
                ctx._source.workflow.steps[stepIndex] =stepHash;
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
