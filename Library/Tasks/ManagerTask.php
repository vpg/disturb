<?php
namespace Disturb\Tasks;

use Phalcon\Cli\Task;

class ManagerTask extends \Disturb\Tasks\AbstractTask
{
    protected $taskOptionList = [
        '?name:'    // optional workflow name
    ];
    
    protected function usage()
    {
        echo PHP_EOL . 'Usage : ';
        echo PHP_EOL . 'disturb.php "Tasks\\Manager" start --workflow="/path/to/workflow/condfig/file.json" [--name="workflowName"]';
        echo PHP_EOL;
    }

    protected function initWorker(array $paramHash)
    {
        echo PHP_EOL . '>' . __METHOD__ . ' : ' . json_encode(func_get_args());
        parent::initWorker($paramHash);
        $serviceFullName = $this->workflowConfig['servicesClassNameSpace'] . '\\' . ucFirst($this->workflowConfig['name']);
        echo PHP_EOL . "Loading $serviceFullName";
        $this->service = new $serviceFullName($paramHash['workflow']);
        // xxx factorise the topicname "build"
        $this->topicName = 'disturb-' . $this->workflowConfig['name'] . '-manager';
    }

    protected function processMessage(\Disturb\Dtos\Message $payloadHash)
    {
        echo PHP_EOL . '>' . __METHOD__ . " : $payloadHash";
        $status = $this->service->getStatus($payloadHash['contract']);
        echo PHP_EOL . "Contact {$payloadHash['contract']} is '$status'";
        switch($payloadHash['type']) {
        case \Disturb\Dtos\Message::TYPE_WF_CTRL:
            switch($payloadHash['action']) {
            case 'start':
                $this->service->init($payloadHash['contract']);
                $this->runNextStep($payloadHash['contract']);
                break;
            }
            break;
        case \Disturb\Dtos\Message::TYPE_STEP_ACK:
            echo PHP_EOL . "Step {$payloadHash['step']} says {$payloadHash['result']}";
            $stepResultHash = json_decode($payloadHash['result'], true);
            $step = $this->service->finalizeStep($payloadHash['contract'], $payloadHash['step'], $stepResultHash);
            $this->runNextStep($payloadHash['contract']);
            break;
        default :
            echo PHP_EOL . "ERR : Unknown message type : {$payloadHash['type']}";
        }
    }

    protected function runNextStep(string $workflowProcessId) {
        $stepTaskHashList = $this->service->getNextStepTaskList($workflowProcessId);
        foreach($stepTaskHashList as $stepTaskHash) {
            $stepCode = $stepTaskHash['name'];
            $stepHash = $this->service->getStepPayload($workflowProcessId, $stepCode);
            foreach($stepHash as $stepJob) {
                $stepMessageDto = new \Disturb\Dtos\Message(json_encode($stepJob));
                $stepMessageDto['type'] = \Disturb\Dtos\Message::TYPE_STEP_CTRL;
                $this->sendMessage('disturb-' . $stepCode . '-step', $stepMessageDto);
            }
        }
    }
}
