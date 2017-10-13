<?
namespace Disturb\Services;

use \Disturb\Exceptions;

class WorkflowManager extends \Phalcon\Mvc\User\Component implements WorkflowManagerInterface
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
        $this->config = new \Phalcon\Config\Adapter\Json($workflowConfigFilePath);
    }

    public function init(string $workflowProcessId) {
        echo PHP_EOL . '>' . __METHOD__ . ' : ' . json_encode(func_get_args());
        $this->tmpStorage[$workflowProcessId] = [
            'workflow' => ['steps' => $this->config['steps']->toArray()],
            'status' => self::STATUS_STARTED,
            'currentStepPos' => -1,
            'initializedAt' => date('Y-m-d H:i:s'),
            'updatedAt' => date('Y-m-d H:i:s')
        ];
    }

    public function getStatus(string $workflowProcessId) : string {
        echo PHP_EOL . '>' . __METHOD__ . ' : ' . json_encode(func_get_args());
        if (!isset($this->tmpStorage[$workflowProcessId]) || empty($this->tmpStorage[$workflowProcessId]['status'])) {
            return self::STATUS_NO_STARTED;
        }
        return $this->tmpStorage[$workflowProcessId]['status'];
    }

    public function getNextStepList(string $workflowProcessId) : array {
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

    // xxx comment
    public function processStepJobResult(string $workflowProcessId, string $stepCode, int $jobId, array $resultHash) {
        echo PHP_EOL . '>' . __METHOD__ . ' : ' . json_encode(func_get_args());
        var_dump($this->tmpStorage);
        // q&d search in context the job for which saving the result
        $stepJobList = null;
        foreach ($this->tmpStorage[$workflowProcessId]['workflow']['steps'] as &$stepNode) {
            var_dump($stepNode);
            if ($this->isStepParallelized($stepNode)) {
                echo '//';
                foreach ($stepNode as &$stepHash) {
                    if ($stepHash['name'] == $stepCode) {
                        $stepJobList = &$stepHash['jobList'];
                        break;
                    }
                }
            } else {
                $stepHash = &$stepNode;
                if ($stepHash['name'] = $stepCode) {
                    echo 'FOUUUUND';
                    $stepJobList = &$stepHash['jobList'];
                    break;
                }
            }


        }
        if (!$stepJobList) {
            // Exc
         }
        var_dump($stepJobList);
        foreach($stepJobList as $id => &$jobHash) {
            if ($jobId != $id) continue;
            $jobHash['result'] = $resultHash;
            var_dump($jobHash);

        }
        var_dump($this->tmpStorage);
    }

    public function registerStepJob($workflowProcessId, $stepCode, $jobId) {
        echo PHP_EOL . '>' . __METHOD__ . ' : ' . json_encode(func_get_args());
        var_dump($this->tmpStorage);
        // q&d search in context the job for which saving the result
        foreach ($this->tmpStorage[$workflowProcessId]['workflow']['steps'] as &$stepNode) {
            var_dump($stepNode);
            if ($this->isStepParallelized($stepNode)) {
                echo '//';
                foreach ($stepNode as &$stepHash) {
                    if ($stepHash['name'] == $stepCode) {
                        if ($stepHash['jobList']) {
                            $stepHash['jobList'][] = [
                                'id' => $jobId,
                                'result' => []
                            ];
                        } else {
                            $stepHash['jobList'] = [[
                                'id' => $jobId,
                                'result' => []
                            ]];
                        }
                        return;
                    }
                }
            } else {
                $stepHash = &$stepNode;
                echo '--';
                if ($stepHash['name'] = $stepCode) {
                    echo 'found';
                        if ($stepHash['jobList']) {
                            echo 'KO';
                            $stepHash['jobList'][] = [
                                'id' => $jobId,
                                'result' => []
                            ];
                        } else {
                            echo 'OK';
                            $stepHash['jobList'] = [[
                                'id' => $jobId,
                                'result' => []
                            ]];
                        }
                        return;
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
