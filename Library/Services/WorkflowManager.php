<?PHP
namespace Vpg\Disturb\Services;

use \Phalcon\Config\Adapter\Json;
use \Phalcon\Mvc\User\Component;
use \Vpg\Disturb\Exceptions;


class WorkflowManager extends Component implements WorkflowManagerInterface
{
    const STATUS_NO_STARTED = 'NOT_STARTED';
    const STATUS_PAUSED     = 'PAUSED';
    const STATUS_STARTED    = 'STARTED';
    const STATUS_SUCCESS    = 'SUCCESS';
    const STATUS_FAILED     = 'FAILED';
    const STATUS_FINISHED   = 'FINISHED';
    const STATUS_RUNNING    = 'RUNNING';

    private $config = null;

    // xxx MUST be replaced by smthg like Redis
    // xxx MUST be abstracted (e.g. Disturb\Storage::set($k, $v)
    private $tmpStorage = [];

    public function __construct(string $workflowConfigFilePath)
    {
        $this->getDI()->get('logger')->debug("Loading WF from '$workflowConfigFilePath'");
        $this->config = new Json($workflowConfigFilePath);
    }

    /**
     * Initialize workflow
     *
     * @param string $workflowProcessId the wf process identifier
     */
    public function init(string $workflowProcessId)
    {
        $this->getDI()->get('logger')->debug(json_encode(func_get_args()));
        $this->tmpStorage[$workflowProcessId] = [
            'workflow' => ['steps' => $this->config['steps']->toArray()],
            'status' => self::STATUS_STARTED,
            'currentStepPos' => -1,
            'initializedAt' => date('Y-m-d H:i:s'),
            'updatedAt' => date('Y-m-d H:i:s')
        ];
    }

    /**
     * Set workflow status
     *
     * @param string $workflowProcessId the wf process identifier
     * @param string $status wf status
     */
    public function setStatus(string $workflowProcessId, string $status)
    {
        $this->getDI()->get('logger')->debug(json_encode(func_get_args()));
        if (isset($this->tmpStorage[$workflowProcessId])) {
            $this->tmpStorage[$workflowProcessId]['status'] = $status;
        }
    }

    /**
     * Get workflow status
     *
     * @param string $workflowProcessId the wf process identifier
     * @return string
     */
    public function getStatus(string $workflowProcessId) : string
    {
        $this->getDI()->get('logger')->debug(json_encode(func_get_args()));
        if (!isset($this->tmpStorage[$workflowProcessId]) || empty($this->tmpStorage[$workflowProcessId]['status'])) {
            return self::STATUS_NO_STARTED;
        }
        return $this->tmpStorage[$workflowProcessId]['status'];
    }

    /**
     * Go to next step
     *
     * @param string $workflowProcessId the wf process identifier
     */
    public function initNextStep(string $workflowProcessId)
    {
        $this->getDI()->get('logger')->debug(json_encode(func_get_args()));
        $this->tmpStorage[$workflowProcessId]['currentStepPos']++;
    }

    /**
     * Get next step if it exists
     *
     * @param string $workflowProcessId the wf process identifier
     * @return array
     */
    public function getNextStepList(string $workflowProcessId) : array
    {
        $this->getDI()->get('logger')->debug(json_encode(func_get_args()));
        $nextStepPos = $this->tmpStorage[$workflowProcessId]['currentStepPos'] + 1;

        // Manage case when there is no more step to run
        if(empty($this->config->steps[$nextStepPos])) {
            $this->setStatus($workflowProcessId, self::STATUS_FINISHED);
            return [];
        }

        $stepNode = $this->config->steps[$nextStepPos]->toArray();
        if (!$this->isStepParallelized($stepNode)) {
            return [$stepNode];
        }
        return $stepNode;
    }

    /**
     * Check for a step if all related jobs have been succeed
     *
     * @param array $step context step hash
     * @return string
     */
    private function getStepStatusByJobStatus(array $step) : string
    {
        $this->getDI()->get('logger')->debug(json_encode(func_get_args()));

        $jobStatusList = [];
        $jobList = $step['jobList'];

        foreach ($jobList as $job) {
            array_push($jobStatusList, $job['status']);
        }

        return $this->aggregateStatus($jobStatusList);
    }

    /**
     * Get global status of a step / job
     *
     * @param array $statusList list of all step / job statuses
     * @return string
     */
    private function aggregateStatus(array $statusList) : string
    {
        $nbJobs = sizeof($statusList);
        $statusValueList = array_count_values($statusList);
        $status = self::STATUS_FAILED;
        // When all steps / jobs have the same status, return it
        if (in_array($nbJobs, $statusValueList)) {
            $status = $statusList[0];
        } else {
            // If there is one running
            if (isset($statusValueList[self::STATUS_NO_STARTED])) {
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
     * Return current step position in the workflow
     *
     * @param string $workflowProcessId the wf process identifier
     * @return int
     */
    private function getWorkflowCurrentPosition(string $workflowProcessId) : int
    {
        $this->getDI()->get('logger')->debug(json_encode(func_get_args()));
        return $this->tmpStorage[$workflowProcessId]['currentStepPos'];
    }

    /**
     * Check current step status and if we can go further in the workflow
     *
     * @param string $workflowProcessId the wf process identifier
     * @return string
     */
    public function getCurrentStepStatus(string $workflowProcessId) : string
    {
        $this->getDI()->get('logger')->debug(json_encode(func_get_args()));
        $currentStepPos = $this->getWorkflowCurrentPosition($workflowProcessId);
        $stepNode = $this->tmpStorage[$workflowProcessId]['workflow']['steps'][$currentStepPos];
        $stepStatusList = [];

        if ($this->isStepParallelized($stepNode))
        {
            foreach ($stepNode as $stepHash)
            {
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
     * @throws \Exception in case of invalid message
     *
     * @return void
     */
    public function processStepJobResult(string $workflowProcessId, string $stepCode, int $jobId, array $resultHash)
    {
        $this->getDI()->get('logger')->debug(json_encode(func_get_args()));
        $stepHash = &$this->getContextStepHashRef($workflowProcessId, $stepCode);
        if (!isset($stepHash['jobList']) || !isset($stepHash['jobList'][$jobId])) {
            throw new Exceptions\WorkflowException('Cannot find any job');
        }
        $stepHash['jobList'][$jobId]['status'] = $resultHash['status'] ?? self::STATUS_FAILED;
        $stepHash['jobList'][$jobId]['result'] = $resultHash['data'] ?? [];

        if ($stepHash['jobList'][$jobId]['status'] == self::STATUS_FAILED) {
            $this->processStepJobFailure($workflowProcessId, $stepCode, $jobId, $resultHash);
        }
    }

    /**
     * Process failure on step job
     *
     * @param string $workflowProcessId the wf process identifier to which belongs the step's job result
     * @param string $stepCode          the step to which belongs the job
     * @param int $jobId                the job identifier related to the step
     * @param array $resultHash         the result data
     */
    private function processStepJobFailure(string $workflowProcessId, string $stepCode, int $jobId, array $resultHash)
    {
        // check step conf to see if the step is "blocking"
        // set the WF status accordingly
        $this->setStatus($workflowProcessId, self::STATUS_FAILED);
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
        $this->getDI()->get('logger')->debug(json_encode(func_get_args()));
        // q&d search in context the job for which saving the result
        $stepHash = &$this->getContextStepHashRef($workflowProcessId, $stepCode);
        if (!isset($stepHash['jobList'])) {
            $stepHash['jobList'] = [];
        }
        // TODO check if job hasn't been registered yet
        $stepHash['jobList'][] = [
            'id' => $jobId,
            'status' => self::STATUS_NO_STARTED,
            'result' => []
        ];
    }

    /**
     * Access step
     *
     * @param $workflowProcessId
     * @param $stepCode
     * @return mixed
     */
    private function &getContextStepHashRef($workflowProcessId, $stepCode)
    {
        $this->getDI()->get('logger')->debug(json_encode(func_get_args()));
        foreach ($this->tmpStorage[$workflowProcessId]['workflow']['steps'] as &$stepNode) {
            if ($this->isStepParallelized($stepNode)) {
                foreach ($stepNode as &$stepHash) {
                    if ($stepHash['name'] == $stepCode) {
                        return $stepHash;
                    }
                }
            } else {
                $stepHash = &$stepNode;
                if ($stepHash['name'] == $stepCode) {
                    return $stepHash;
                }
            }
        }
    }

    /**
     * Check whether the workflow is running or not
     *
     * @param string $workflowProcessId
     * @return bool
     */
    private function isRunning(string $workflowProcessId) : bool
    {
        $this->getDI()->get('logger')->debug(json_encode(func_get_args()));
        return ($this->tmpStorage[$workflowProcessId]['status'] == self::STATUS_STARTED);
    }

    /**
     * Check if the step is parallelized
     *
     * @param $stepNode
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
        $this->getDI()->get('logger')->debug(json_encode(func_get_args()));
        return !(array_keys($stepNode) !== array_keys(array_keys($stepNode)));
    }

}
