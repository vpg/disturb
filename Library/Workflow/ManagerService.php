<?PHP
namespace Vpg\Disturb\Workflow;

use \Phalcon\Mvc\User\Component;

use Vpg\Disturb\Context\ContextStorageService;
use Vpg\Disturb\Workflow\WorkflowConfigDto;

/**
 * Class WorkflowManager
 *
 * @package  Disturb\Workflow
 * @author   Jérome BOURGEAIS <jbourgeais@voyageprive.com>
 * @license  https://github.com/vpg/disturb/blob/master/LICENSE MIT Licence
 */
class ManagerService extends Component implements WorkflowManagerInterface
{
    /**
     * Status no started const
     *
     * @const string STATUS_NO_STARTED
     */
    const STATUS_NO_STARTED = 'NOT_STARTED';

    /**
     * Status paused const
     *
     * @const string STATUS_PAUSED
     */
    const STATUS_PAUSED = 'PAUSED';

    /**
     * Status started const
     *
     * @const string STATUS_STARTED
     */
    const STATUS_STARTED = 'STARTED';

    /**
     * Status success const
     *
     * @const string STATUS_SUCCESS
     */
    const STATUS_SUCCESS = 'SUCCESS';

    /**
     * Status failed const
     *
     * @const string STATUS_FAILED
     */
    const STATUS_FAILED = 'FAILED';

    /**
     * Status running const
     *
     * @const string STATUS_RUNNING
     */
    const STATUS_RUNNING = 'RUNNING';


    private $workflowConfig;

    /**
     * WorkflowManager constructor.
     *
     * @param WorkflowConfigDto $workflowConfigDto config
     */
    public function __construct(WorkflowConfigDto $workflowConfigDto)
    {
        $this->workflowConfig = $workflowConfigDto;
        $this->di->setShared('contextStorage', new ContextStorageService($workflowConfigDto));
    }

    /**
     * Init
     *
     * @param string $workflowProcessId the workflow process identifier
     * @param array  $payloadHash       the workflow initial payload
     * @param string $workerHostname    the worker on which the WF has been init
     *
     * @return void
     */
    public function init(string $workflowProcessId, array $payloadHash, string $workerHostname)
    {
        $this->di->get('logr')->debug(json_encode(func_get_args()));
        if ($this->di->get('contextStorage')->exists($workflowProcessId)) {
            throw new WorkflowException("Failed to init workflow '$workflowProcessId' : existing context");
        }
        $this->di->get('contextStorage')->save(
            $workflowProcessId,
            [
                'steps' => $this->workflowConfig->getStepList(),
                'initialPayload' => $payloadHash,
                'status' => self::STATUS_STARTED,
                'currentStepPos' => -1,
                'startedAt' => date(ContextStorageService::DATE_FORMAT),
                'startedOn' => $workerHostname
                ]
        );
    }

    /**
     * Finalize the given workflow
     *
     * @param string $workflowProcessId the workflow process identifier
     * @param string $workflowStatus    the workflow status
     * @param string $workflowInfo      the workflow info
     *
     * @return void
     */
    public function finalize(string $workflowProcessId, string $workflowStatus, string $workflowInfo = '')
    {
        $this->di->get('logr')->debug(json_encode(func_get_args()));
        if (!$this->di->get('contextStorage')->exists($workflowProcessId)) {
            throw new WorkflowException("Failed to finaliz workflow '$workflowProcessId' : non existing context");
        }
        $this->di->get('contextStorage')->save(
            $workflowProcessId,
            [
                'status'     => $workflowStatus,
                'finishedAt' => date(ContextStorageService::DATE_FORMAT),
                'info'       => $workflowInfo
            ]
        );
    }

    /**
     * Returns the context of the given workflow
     *
     * @param string $workflowProcessId the workflow process identifier
     *
     * @return Context\ContextDto
     */
    public function getContext(string $workflowProcessId)
    {
        $this->di->get('logr')->debug(json_encode(func_get_args()));
        return $this->di->get('contextStorage')->get($workflowProcessId);
    }

    /**
     * Set workflow status
     *
     * @param string $workflowProcessId the wf process identifier
     * @param string $status            wf status
     * @param string $message           error message
     *
     * @return void
     */
    public function setStatus(string $workflowProcessId, string $status, string $message = '')
    {
        $this->di->get('logr')->debug(json_encode(func_get_args()));
        $this->di->get('logr')->info("Workflow $workflowProcessId is now $status");
        $this->di->get('contextStorage')->setWorkflowStatus($workflowProcessId, $status, $message);
    }

    /**
     * Get workflow status
     *
     * @param string $workflowProcessId the workflow process identifier
     *
     * @return string
     */
    public function getStatus(string $workflowProcessId) : string
    {
        $this->di->get('logr')->debug(json_encode(func_get_args()));
        $contextDto = $this->di->get('contextStorage')->get($workflowProcessId);
        return $contextDto->getWorkflowStatus();
    }

    /**
     * Go to next step
     *
     * @param string $workflowProcessId the workflow process identifier
     *
     * @return void
     */
    public function initNextStep(string $workflowProcessId)
    {
        $this->di->get('logr')->debug(json_encode(func_get_args()));
        $this->di->get('contextStorage')->initWorkflowNextStep($workflowProcessId);
    }

    /**
     * Get next step if it exists
     *
     * @param string $workflowProcessId the wf process identifier
     *
     * @return array
     */
    public function getNextStepList(string $workflowProcessId) : array
    {
        $this->di->get('logr')->debug(json_encode(func_get_args()));
        $contextDto = $this->di->get('contextStorage')->get($workflowProcessId);
        $nextStepPos = $contextDto->getWorkflowCurrentPosition() + 1;

        // Manage case when there is no more step to run
        if (empty($this->workflowConfig->getStepList()[$nextStepPos])) {
            throw new WorkflowException('No more step to process, call Manager::hasNextStep() first');
        }

        $stepNode = $this->workflowConfig->getStepList()[$nextStepPos];
        if (!$this->isStepParallelized($stepNode)) {
            return [$stepNode];
        }
        return $stepNode;
    }

    /**
     * Returns true if the given workflow has still step to run
     *
     * @param string $workflowProcessId the wf process identifier
     *
     * @return bool
     */
    public function hasNextStep(string $workflowProcessId) : bool
    {
        $this->di->get('logr')->debug(json_encode(func_get_args()));
        $contextDto = $this->di->get('contextStorage')->get($workflowProcessId);
        $nextStepPos = $contextDto->getWorkflowCurrentPosition() + 1;
        return !empty($this->workflowConfig->getStepList()[$nextStepPos]);
    }


    /**
     * Check for a step if all related jobs have been succeed
     *
     * @param array $step context step hash
     *
     * @return string
     */
    private function getStepStatusByJobStatus(array $step) : string
    {
        $this->di->get('logr')->debug(json_encode(func_get_args()));

        $jobStatusList = [];
        $jobList = $step['jobList'];

        if (empty($jobList) && isset($step['skippedAt'])) {
            return self::STATUS_SUCCESS;
        }

        foreach ($jobList as $job) {
            array_push($jobStatusList, $job['status']);
        }

        return $this->aggregateStatus($jobStatusList);
    }

    /**
     * Get global status of a step / job
     *
     * @param array $statusList list of all step / job statuses
     *
     * @return string
     */
    private function aggregateStatus(array $statusList) : string
    {
        $this->di->get('logr')->debug(json_encode(func_get_args()));
        $nbJobs = sizeof($statusList);
        $statusValueList = array_count_values($statusList);
        $status = self::STATUS_FAILED;
        // When all steps / jobs have the same status, return it
        if (in_array($nbJobs, $statusValueList)) {
            $status = $statusList[0];
        } else {
            // If there is one running
            if (
                isset($statusValueList[self::STATUS_NO_STARTED]) ||
                isset($statusValueList[self::STATUS_RUNNING]) ||
                isset($statusValueList[self::STATUS_STARTED])
            ) {
                $status = self::STATUS_RUNNING;
            }
            // If there is one fail
            if (isset($statusValueList[self::STATUS_FAILED])) {
                $status = self::STATUS_FAILED;
            }
        }
        return $status;
    }

    /**
     * Check current step status and if we can go further in the workflow
     *
     * @param string $workflowProcessId the wf process identifier
     *
     * @return string
     */
    public function getCurrentStepStatus(string $workflowProcessId) : string
    {
        $this->di->get('logr')->debug(json_encode(func_get_args()));
        $contextDto = $this->di->get('contextStorage')->get($workflowProcessId);
        $currentStepPos = $contextDto->getWorkflowCurrentPosition();
        $stepNode = $contextDto->getWorkflowStepListByPosition($currentStepPos);
        $stepStatusList = [];

        if ($this->isStepParallelized($stepNode)) {
            foreach ($stepNode as $stepHash) {
                array_push($stepStatusList, $this->getStepStatusByJobStatus($stepHash));
            }
            $status = $this->aggregateStatus($stepStatusList);
        } else {
            $status = $this->getStepStatusByJobStatus($stepNode);
        }

        return $status;
    }

    /**
     * Parses and stores the step's job results related to the given wf process id and step
     * Result is stored in the context as below :
     *  {
     *      'jobList' : [
     *          {
     *              'jobId' : 0,
     *              'status' : 'SUCCESS',
     *              'result' : {
     *                  // biz data
     *              }
     *          },
     *      ]
     *  }
     *
     * @param string $workflowProcessId the wf process identifier to which belongs the step's job result
     * @param string $stepCode          the step to which belongs the job
     * @param int    $jobId             the job identifier related to the step
     * @param array  $resultHash        the result data
     *
     * @throws WorkflowException in case of no job found
     * @throws WorkflowJobFinalizationException in case of ack received twice or more
     *
     * @return void
     */
    public function processStepJobResult(string $workflowProcessId, string $stepCode, int $jobId, array $resultHash)
    {
        $this->di->get('logr')->debug(json_encode(func_get_args()));
        $stepHash = $this->getContextWorkflowStep($workflowProcessId, $stepCode);
        if (!isset($stepHash['jobList']) || !isset($stepHash['jobList'][$jobId])) {
            throw new WorkflowException('Cannot find any job');
        }
        $jobStatus = $resultHash['status'] ?? self::STATUS_FAILED;
        $jobResult = $resultHash['data'] ?? [];
        $jobFinishedAt = $resultHash['finishedAt'] ?? date(ContextStorageService::DATE_FORMAT);
        $updateResultHash = $this->di->get('contextStorage')->finalizeWorkflowStepJob(
            $workflowProcessId,
            $stepCode,
            $jobId,
            $jobStatus,
            $jobFinishedAt,
            $jobResult
        );

        if ($updateResultHash['result'] == 'noop') {
            throw new WorkflowJobFinalizationException(
                "Failed to finalize job workflow#$workflowProcessId/$stepCode#$jobId : Already finalized"
            );
        }

        if ($stepHash['jobList'][$jobId]['status'] == self::STATUS_FAILED) {
            $this->processStepJobFailure($workflowProcessId, $stepCode, $jobId, $resultHash);
        }
    }

    /**
     * Process failure on step job
     *
     * @param string $workflowProcessId the wf process identifier to which belongs the step's job result
     * @param string $stepCode          the step to which belongs the job
     * @param int    $jobId             the job identifier related to the step
     * @param array  $resultHash        the result data
     *
     * @return void
     */
    private function processStepJobFailure(string $workflowProcessId, string $stepCode, int $jobId, array $resultHash)
    {
        // check step conf to see if the step is "blocking"
        // set the WF status accordingly
        $this->finalize($workflowProcessId, self::STATUS_FAILED, $resultHash['info'] ?? '');
    }

    /**
     * Registers in context the step's job related to the given wf process id
     * Stores in context as below :
     *  {
     *      'jobList' : [
     *          {
     *              'jobId' : 0,
     *              'status' : 'NOT_STARTED',
     *              'result' : {}
     *          },
     *      ]
     *  }
     *
     * @param string $workflowProcessId the wf process identifier to which belongs the step's job result
     * @param string $stepCode          the step to which belongs the job
     * @param int    $jobId             the job identifier related to the step
     *
     * @return void
     */
    public function registerStepJob($workflowProcessId, $stepCode, $jobId)
    {
        $this->di->get('logr')->debug(json_encode(func_get_args()));
        // q&d search in context the job for which saving the result
        $stepHash = $this->getContextWorkflowStep($workflowProcessId, $stepCode);
        if (!isset($stepHash['jobList'])) {
            $stepHash['jobList'] = [];
        }
        // TODO check if job hasn't been registered yet
        $stepHash['jobList'][] = [
            'id' => $jobId,
            'status' => self::STATUS_NO_STARTED,
            'registeredAt' => date(ContextStorageService::DATE_FORMAT),
            'result' => []
        ];
        $this->di->get('contextStorage')->updateWorkflowStep($workflowProcessId, $stepCode, $stepHash);
    }

    /**
     * Registers in context the step's who has no job
     * Stores in context as below :
     *  {
     *      'jobList' : [],
     *      'hasJob' : false
     *  }
     *
     * @param string $workflowProcessId the wf process identifier to which belongs the step's job result
     * @param string $stepCode          the step to which belongs the job
     *
     * @return void
     */
    public function registerStepWithoutJob($workflowProcessId, $stepCode)
    {
        $this->di->get('logr')->debug(json_encode(func_get_args()));
        // q&d search in context the job for which saving the result
        $stepHash = $this->getContextWorkflowStep($workflowProcessId, $stepCode);

        $stepHash['jobList'] = [];
        $stepHash['skippedAt'] = date(ContextStorageService::DATE_FORMAT);
        $this->di->get('contextStorage')->updateWorkflowStep($workflowProcessId, $stepCode, $stepHash);
    }

    /**
     * Updates in context the step's job status related to the given wf process id
     * Stores in context as below :
     *  {
     *      'jobList' : [
     *          {
     *              'jobId' : 0,
     *              'status' : 'NOT_STARTED',
     *
     *              'result' : {}
     *          },
     *      ]
     *  }
     *
     * @param string $workflowProcessId the wf process identifier to which belongs the step's job result
     * @param string $stepCode          the step to which belongs the job
     * @param int    $jobId             the job identifier related to the step
     * @param string $workerHostname    the worker hostname on which the step has been executed
     *
     * @return void
     */
    public function registerStepJobStarted($workflowProcessId, $stepCode, $jobId, $workerHostname)
    {
        $this->di->get('logr')->debug(json_encode(func_get_args()));
        $this->di->get('contextStorage')->updateWorkflowStepJob(
            $workflowProcessId,
            $stepCode,
            $jobId,
            [
                'status' => self::STATUS_STARTED,
                'startedAt' => date(ContextStorageService::DATE_FORMAT),
            ]
        );
    }

    /**
     * Reserves a jobb to process if it has not been already reserved
     *
     * @param string $workflowProcessId the wf process identifier to which belongs the step's job result
     * @param string $stepCode          the step to which belongs the job
     * @param int    $jobId             the job identifier related to the step
     * @param string $workerHostname    the worker hostname on which the step has been executed
     * @param string $workerCode        the worker instance code
     *
     * @return void
     */
    public function reserveStepJob(
        string $workflowProcessId,
        string $stepCode,
        int $jobId,
        string $workerHostname,
        string $workerCode
    ) {
        $this->di->get('logr')->debug(json_encode(func_get_args()));
        $reservationResultHash = $this->di->get('contextStorage')->reserveJob(
            $workflowProcessId,
            $stepCode,
            $jobId,
            $workerHostname,
            $workerCode
        );
        $this->di->get('logr')->debug('Reservation result : ' . json_encode($reservationResultHash));
        // check if the reservation has been made
        if ($reservationResultHash['result'] == 'noop') {
            throw new WorkflowJobReservationException(
                "Failed to reserve job workflow#$workflowProcessId/$stepCode#$jobId : Already reserved"
            );
        }
    }

    /**
     * Access step
     *
     * @param string $workflowProcessId the wf process identifier to which belongs the step's job result
     * @param string $stepCode          the step to which belongs the job
     *
     * @return mixed
     */
    private function getContextWorkflowStep($workflowProcessId, $stepCode)
    {
        $this->di->get('logr')->debug(json_encode(func_get_args()));
        $contextDto = $this->di->get('contextStorage')->get($workflowProcessId);
        $workflowStepList = $contextDto->getWorkflowStepList();
        foreach ($workflowStepList as &$stepNode) {
            if ($this->isStepParallelized($stepNode)) {
                foreach ($stepNode as &$stepHash) {
                    if ($stepHash['name'] == $stepCode) {
                        return $stepHash;
                    }
                }
            } else {
                $stepHash = $stepNode;
                if ($stepHash['name'] == $stepCode) {
                    return $stepHash;
                }
            }
        }
    }

    /**
     * Check if the step is parallelized
     *
     * @param string $stepNode step node
     *
     * @return bool
     */
    private function isStepParallelized($stepNode) : bool
    {
        // Deals w/ parallelized task xxx to unitest
        // To distinguish single step hash :
        // { "name" : "step_foo"}
        // from
        // [
        //      { "name" : "step_foo"},
        //      { "name" : "step_bar"}
        // ]
        $this->di->get('logr')->debug(json_encode(func_get_args()));
        return !(array_keys($stepNode) !== array_keys(array_keys($stepNode)));
    }
}
