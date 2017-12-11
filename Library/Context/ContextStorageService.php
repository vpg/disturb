<?php
namespace Vpg\Disturb\Context;

use \Phalcon\Config;
use \Phalcon\Mvc\User\Component;

use Vpg\Disturb\Core\Storage;
use Vpg\Disturb\Workflow;

/**
 * Class ContextStorage
 *
 * @package  Disturb\Context
 * @author   Alexandre DEFRETIN <adefretin@voyageprive.com>
 * @license  https://github.com/vpg/disturb/blob/master/LICENSE MIT Licence
 */
class ContextStorageService extends Component
{
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
     * Workflow info
     *
     * @const string WORKFLOW_INFO
     */
    const WORKFLOW_INFO = 'info';

    /**
     * Workflow current step pos
     *
     * @const string WORKFLOW_CURRENT_STEP_POS
     */
    const WORKFLOW_CURRENT_STEP_POS = 'currentStepPos';

    /**
     * @const string DATE_FORMAT the date format used to store date
     */
    const DATE_FORMAT = 'Y-m-d H:i:s';

    /**
     * Adapter
     *
     * @var StorageAdapterInterface $adapter
     */
    private $adapter;

    /**
     * ContextStorage constructor
     *
     * @param string $configFilePath config file path
     *
     * @throws StorageException
     */
    public function __construct(string $configFilePath)
    {
        $this->di->get('logr')->debug(json_encode(func_get_args()));
        $config = new Workflow\WorkflowConfigDto($configFilePath);
        $this->adapter = Storage\StorageAdapterFactory::get(
            $config,
            Storage\StorageAdapterFactory::USAGE_CONTEXT
        );
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
        $this->di->get('logr')->debug(json_encode(func_get_args()));
        $contextHash = $this->adapter->get($workflowProcessId);
        return new ContextDto($contextHash);
    }

    /**
     * Check if storage date $workflowProcessId exists
     *
     * @param string $workflowProcessId the workflow process id
     *
     * @return bool
     */
    public function exists(string $workflowProcessId)
    {
        $this->di->get('logr')->debug(json_encode(func_get_args()));
        return $this->adapter->exists($workflowProcessId);
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
        $this->di->get('logr')->debug(json_encode(func_get_args()));
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
        $this->di->get('logr')->debug(json_encode(func_get_args()));
        return $this->adapter->delete($workflowProcessId);
    }

    /**
     * Set workflow status
     *
     * @param string $workflowProcessId the workflow process id
     * @param string $status            status
     * @param string $info              status info related
     *
     * @return void
     */
    public function setWorkflowStatus(string $workflowProcessId, string $status, string $info = '')
    {
        $this->di->get('logr')->debug(json_encode(func_get_args()));
        $contextDto = $this->get($workflowProcessId);
        $contextHash = $contextDto->getRawHash();
        $contextHash[self::WORKFLOW_STATUS] = $status;
        $contextHash[self::WORKFLOW_INFO] = $info;
        $this->save($workflowProcessId, $contextHash);

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
        $this->di->get('logr')->debug(json_encode(func_get_args()));
        $contextDto = $this->get($workflowProcessId);
        $contextHash = $contextDto->getRawHash();
        $contextHash[self::WORKFLOW_CURRENT_STEP_POS]++;
        $this->save($workflowProcessId, $contextHash);
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
        $this->di->get('logr')->debug(json_encode(func_get_args()));
        // xxx use partial update
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
        $this->di->get('logr')->debug(json_encode(func_get_args()));
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
                ctx._source.workflow.steps[stepIndex] = stepHash;
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

    /**
     * update the given step job related to the given workflow/stepcode/jobid
     *
     * @param string $workflowProcessId the workflow identifier
     * @param string $stepCode          the step code
     * @param int    $jobId             the step job id
     * @param array  $jobHash           the job data to save
     *
     * @return array
     */
    public function updateWorkflowStepJob(string $workflowProcessId, string $stepCode, int $jobId, array $jobHash)
    {
        $this->di->get('logr')->debug(json_encode(func_get_args()));
        $script = <<<eot
        def nbStep = ctx._source.workflow.steps.size();
        for (stepIndex = 0; stepIndex < nbStep; stepIndex++) {
            if (ctx._source.workflow.steps[stepIndex] instanceof List) {
                def nbParallelizedStep = ctx._source.workflow.steps[stepIndex].size();
                for (parallelizedStepIndex= 0; parallelizedStepIndex< nbParallelizedStep; parallelizedStepIndex++) {
                    if (ctx._source.workflow.steps[stepIndex][parallelizedStepIndex].name == stepCode) {
                        def nbJob = ctx._source.workflow.steps[stepIndex][parallelizedStepIndex]['jobList'].size();
                        for (jobIndex = 0; jobIndex < nbJob; jobIndex++) {
                            if (ctx._source.workflow.steps[stepIndex][parallelizedStepIndex]['jobList'][jobIndex].id == jobId) {
                                ctx._source.workflow.steps[stepIndex][parallelizedStepIndex]['jobList'][jobIndex] << jobHash
                                break;
                            }
                         }
                        break;
                    }
                }
            } else if (ctx._source.workflow.steps[stepIndex].name == stepCode) {
                def nbJob = ctx._source.workflow.steps[stepIndex]['jobList'].size();
                for (jobIndex = 0; jobIndex < nbJob; jobIndex++) {
                    if (ctx._source.workflow.steps[stepIndex]['jobList'][jobIndex].id == jobId) {
                        ctx._source.workflow.steps[stepIndex]['jobList'][jobIndex] << jobHash
                        break;
                    }
                }
            }
        }
eot;
        $updateHash = [
            'script' => [
                'lang' => 'groovy',
                'inline' => $script,
                'params' => [
                    'stepCode' => $stepCode,
                    'jobId' => $jobId,
                    'jobHash' => $jobHash
                ]
            ]
        ];
        return $this->adapter->update($workflowProcessId, $updateHash, 2);
    }


}
