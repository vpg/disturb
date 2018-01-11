<?php
namespace Vpg\Disturb\Context;

use \Phalcon\Mvc\User\Component;

use Vpg\Disturb\Core\Storage;
use Vpg\Disturb\Workflow\WorkflowConfigDto;

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
     * @param WorkflowConfigDto $workflowConfigDto config
     *
     * @throws StorageException
     */
    public function __construct(WorkflowConfigDto $workflowConfigDto)
    {
        $this->di->get('logr')->debug(json_encode(func_get_args()));
        $this->adapter = Storage\StorageAdapterFactory::get(
            $workflowConfigDto,
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
        $contextHash[self::WORKFLOW_STEPS] = $workflowStepList;
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
        def nbStep = ctx._source.steps.size();
        for (stepIndex = 0; stepIndex < nbStep; stepIndex++) {
            if (ctx._source.steps[stepIndex] instanceof List) {
                def nbParallelizedStep = ctx._source.steps[stepIndex].size();
                for (parallelizedStepIndex = 0; parallelizedStepIndex < nbParallelizedStep; parallelizedStepIndex++) {
                    if (ctx._source.steps[stepIndex][parallelizedStepIndex].name == stepCode) {
                        ctx._source.steps[stepIndex][parallelizedStepIndex] = stepHash;
                        break;
                    }
                }
            } else if (ctx._source.steps[stepIndex].name == stepCode) {
                ctx._source.steps[stepIndex] = stepHash;
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
        def nbStep = ctx._source.steps.size();
        for (stepIndex = 0; stepIndex < nbStep; stepIndex++) {
            if (ctx._source.steps[stepIndex] instanceof List) {
                def nbParallelizedStep = ctx._source.steps[stepIndex].size();
                for (parallelizedStepIndex= 0; parallelizedStepIndex< nbParallelizedStep; parallelizedStepIndex++) {
                    if (ctx._source.steps[stepIndex][parallelizedStepIndex].name == stepCode) {
                        def nbJob = ctx._source.steps[stepIndex][parallelizedStepIndex]['jobList'].size();
                        for (jobIndex = 0; jobIndex < nbJob; jobIndex++) {
                            if (ctx._source.steps[stepIndex][parallelizedStepIndex]['jobList'][jobIndex].id == jobId) {
                                ctx._source.steps[stepIndex][parallelizedStepIndex]['jobList'][jobIndex] << jobHash
                                break;
                            }
                         }
                        break;
                    }
                }
            } else if (ctx._source.steps[stepIndex].name == stepCode) {
                def nbJob = ctx._source.steps[stepIndex]['jobList'].size();
                for (jobIndex = 0; jobIndex < nbJob; jobIndex++) {
                    if (ctx._source.steps[stepIndex]['jobList'][jobIndex].id == jobId) {
                        ctx._source.steps[stepIndex]['jobList'][jobIndex] << jobHash
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
        // xxx put the retry nb in conf
        return $this->adapter->update($workflowProcessId, $updateHash, $retryNb = 2);
    }

    /**
     * update the given step job related to the given workflow/stepcode/jobid
     *
     * @param string $workflowProcessId the workflow identifier
     * @param string $stepCode          the step code
     * @param int    $jobId             the step job id
     * @param string $workerHostname    the worker hostname on which the step has been executed
     * @param string $workerCode        the worker instance code
     *
     * @return array
     */
    public function reserveJob(
        string $workflowProcessId,
        string $stepCode,
        int $jobId,
        string $workerHostname,
        string $workerCode
    ) {
        $this->di->get('logr')->debug(json_encode(func_get_args()));
        $script = <<<eot
        def nbStep = ctx._source.steps.size();
        def jobHash = [reservedBy:workerCode, executedOn:workerHostname];
        // loop over steps
        for (stepIndex = 0; stepIndex < nbStep; stepIndex++) {
            def step = ctx._source.steps[stepIndex];
            // if its a parrallelized steps node, loop over each
            if (step instanceof List) {
                def nbParallelizedStep = step.size();
                for (parallelizedStepIndex= 0; parallelizedStepIndex< nbParallelizedStep; parallelizedStepIndex++) {
                    // if the given step is found, look for the given job
                    if (step[parallelizedStepIndex].name == stepCode) {
                        def nbJob = step[parallelizedStepIndex]['jobList'].size();
                        for (jobIndex = 0; jobIndex < nbJob; jobIndex++) {
                            def job = step[parallelizedStepIndex]['jobList'][jobIndex];
                            if (job.id == jobId) {
                                // if job's already reserved : noop
                                if (job.containsKey('reservedBy')) {
                                    ctx.op = 'noop';
                                    break;
                                }
                                ctx._source.steps[stepIndex][parallelizedStepIndex]['jobList'][jobIndex] << jobHash
                                break;
                            }
                         }
                        break;
                    }
                }
            } else if (step.name == stepCode) {
                def nbJob = step.jobList.size();
                for (jobIndex = 0; jobIndex < nbJob; jobIndex++) {
                    def job = ctx._source.steps[stepIndex]['jobList'][jobIndex];
                    if (job.id == jobId) {
                        // if job's already reserved : noop
                        if (job.containsKey('reservedBy')) {
                            ctx.op = 'noop';
                            break;
                        }
                        ctx._source.steps[stepIndex]['jobList'][jobIndex] << jobHash
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
                    'workerHostname' => $workerHostname,
                    'workerCode' => $workerCode
                ]
            ]
        ];
        // xxx put the retry nb in conf
        return $this->adapter->update($workflowProcessId, $updateHash, $retryNb = 2);
    }

    /**
     * Sets the given step job related to the given workflow/stepcode/jobid to finished
     *
     * @param string $workflowProcessId the workflow identifier
     * @param string $stepCode          the step code
     * @param int    $jobId             the step job id
     * @param string $workerHostname    the worker hostname on which the step has been executed
     * @param string $workerCode        the worker instance code
     *
     * @return array
     */
    public function finalizeWorkflowStepJob(
        string $workflowProcessId,
        string $stepCode,
        int $jobId,
        string $jobStatus,
        string $jobFinishedAt,
        array $jobResult
    ) {
        $this->di->get('logr')->debug(json_encode(func_get_args()));
        $script = <<<eot
        def nbStep = ctx._source.steps.size();
        def jobHash = [status:jobStatus, finishedAt:jobFinishedAt, data:jobResult];
        // loop over steps
        for (stepIndex = 0; stepIndex < nbStep; stepIndex++) {
            def step = ctx._source.steps[stepIndex];
            // if its a parrallelized steps node, loop over each
            if (step instanceof List) {
                def nbParallelizedStep = step.size();
                for (parallelizedStepIndex= 0; parallelizedStepIndex< nbParallelizedStep; parallelizedStepIndex++) {
                    // if the given step is found, look for the given job
                    if (step[parallelizedStepIndex].name == stepCode) {
                        def nbJob = step[parallelizedStepIndex]['jobList'].size();
                        for (jobIndex = 0; jobIndex < nbJob; jobIndex++) {
                            def job = step[parallelizedStepIndex]['jobList'][jobIndex];
                            if (job.id == jobId) {
                                // if job's already reserved : noop
                                if (job.containsKey('finishedAt')) {
                                    ctx.op = 'noop';
                                    break;
                                }
                                ctx._source.steps[stepIndex][parallelizedStepIndex]['jobList'][jobIndex] << jobHash
                                break;
                            }
                         }
                        break;
                    }
                }
            } else if (step.name == stepCode) {
                def nbJob = step.jobList.size();
                for (jobIndex = 0; jobIndex < nbJob; jobIndex++) {
                    def job = ctx._source.steps[stepIndex]['jobList'][jobIndex];
                    if (job.id == jobId) {
                        // if job's already reserved : noop
                        if (job.containsKey('finishedAt')) {
                            ctx.op = 'noop';
                            break;
                        }
                        ctx._source.steps[stepIndex]['jobList'][jobIndex] << jobHash
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
                    'jobStatus' => $jobStatus,
                    'jobFinishedAt' => $jobFinishedAt,
                    'jobResult' => $jobResult
                ]
            ]
        ];
        // xxx put the retry nb in conf
        return $this->adapter->update($workflowProcessId, $updateHash, $retryNb = 2);
    }
}
