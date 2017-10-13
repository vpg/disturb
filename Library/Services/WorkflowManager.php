<?
namespace Disturb\Services;

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
            'workflow' => $this->config['step'],
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
        // q&d
        foreach ($this->tmpStorage[$workflowProcessId]['workflow']['steps'] as $stepNode) {
            if ($this->isStepParallelized($stepNode)) {

            }

        }
        //$currentStepNo = array_search($stepName, array_column($this->config['steps'], 'name'));
        $currentStepPos = $this->tmpStorage[$workflowProcessId]['currentStepPos'];
        $this->tmpStorage[$workflowProcessId]['workflow']['steps'][$currentStepPos]['result'] = $resultHash;
        $this->tmpStorage[$workflowProcessId]['currentStepPos']++;
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
        return !(array_keys($stepNode) !== array_keys(array_keys($stepNode)))
    }

}
