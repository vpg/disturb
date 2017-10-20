<?PHP
namespace Vpg\Disturb\Services;

use \Phalcon\Config\Adapter\Json;
use \Phalcon\Mvc\User\Component;
use \Vpg\Disturb\Exceptions;


class WorkflowManager extends Component implements WorkflowManagerInterface
{
    const STATUS_NO_STARTED = 'NOT_STARTED';
    const STATUS_PAUSED = 'PAUSED';
    const STATUS_STARTED = 'STARTED';
    const STATUS_SUCCESS = 'SUCCESS';
    const STATUS_FAILED = 'FAILED';

    private $config = null;

    // xxx MUST be replaced by smthg like Redis
    // xxx MUST be abstracted (e.g. Disturb\Storage::set($k, $v)
    private $tmpStorage = [];


    public function __construct(string $workflowConfigFilePath)
    {
        echo PHP_EOL . "Loading WF from '$workflowConfigFilePath'";
        $this->config = new Json($workflowConfigFilePath);
    }

    public function init(string $workflowProcessId)
    {
        echo PHP_EOL . '>' . __METHOD__ . ' : ' . json_encode(func_get_args());
       $this->tmpStorage[$workflowProcessId] = [
            'workflow' => ['steps' => $this->config['steps']->toArray()],
            'status' => self::STATUS_STARTED,
            'currentStepPos' => -1,
            'initializedAt' => date('Y-m-d H:i:s'),
            'updatedAt' => date('Y-m-d H:i:s')
        ];
    }

    public function getStatus(string $workflowProcessId) : string
    {
        echo PHP_EOL . '>' . __METHOD__ . ' : ' . json_encode(func_get_args());
        if (!isset($this->tmpStorage[$workflowProcessId]) || empty($this->tmpStorage[$workflowProcessId]['status'])) {
            return self::STATUS_NO_STARTED;
        }
        return $this->tmpStorage[$workflowProcessId]['status'];
    }

    public function getNextStepList(string $workflowProcessId) : array
    {
        // Check WF status
        echo PHP_EOL . '>' . __METHOD__ . ' : ' . json_encode(func_get_args());
        $nextStepPos = $this->tmpStorage[$workflowProcessId]['currentStepPos'] + 1;
        echo PHP_EOL . '<' . __METHOD__ . ' : ' . $nextStepPos;
        $stepNode = $this->config->steps[$nextStepPos]->toArray();
        if(!$this->isStepParallelized($stepNode)) {
            return [$stepNode];
        }
        return $stepNode;
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
     * @return void
     */
    public function processStepJobResult(string $workflowProcessId, string $stepCode, int $jobId, array $resultHash)
    {
        echo PHP_EOL . '>' . __METHOD__ . ' : ' . json_encode(func_get_args());
        $stepHash = &$this->getContextStepHashRef($workflowProcessId, $stepCode);
        var_dump($stepHash);
        if (!isset($stepHash['jobList']) || !isset($stepHash['jobList'][$jobId])) {
            // not initialized, must not happen
            // Exc
        }
        $stepHash['jobList'][$jobId]['status'] = $resultHash['status'] ?? self::STATUS_FAILED;
        $stepHash['jobList'][$jobId]['result'] = $resultHash['data'] ?? [];
        var_dump($stepHash);
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
        echo PHP_EOL . '>' . __METHOD__ . ' : ' . json_encode(func_get_args());
        // q&d search in context the job for which saving the result
        $stepHash = &$this->getContextStepHashRef($workflowProcessId, $stepCode);
        if (!isset($stepHash['jobList'])) {
            $stepHash['jobList'] = [];
        }
        $stepHash['jobList'][] = [
            'id' => $jobId,
            'status' => self::STATUS_NO_STARTED,
            'result' => []
        ];
    }

    private function &getContextStepHashRef($workflowProcessId, $stepCode)
    {
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

    private function isRunning(string $workflowProcessId) {
        return ($this->tmpStorage[$workflowProcessId]['status'] == self::STATUS_STARTED);
    }

    private function isStepParallelized($stepNode) {
        // Deals w/ parallelized task xxx to unitest
        // To ditinguish single step hash :
        // { "name" : "step_foo"}
        // of
        // [
        //      { "name" : "step_foo"},
        //      { "name" : "step_bar"}
        // ]
        return !(array_keys($stepNode) !== array_keys(array_keys($stepNode)));
    }

}
