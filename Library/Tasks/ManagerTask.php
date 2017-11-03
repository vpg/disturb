<?php
namespace Vpg\Disturb\Tasks;

use Phalcon\Cli\Task;
use \Vpg\Disturb\Services;
use \Vpg\Disturb\Dtos;
use \Vpg\Disturb\Tasks\AbstractTask as AbstractTask;


class ManagerTask extends AbstractTask
{
    protected $taskOptionList = [
        '?name:'    // optional workflow name
    ];
    
    protected function usage()
    {
        // xxx improve usage handling
        $this->getDI()->get('logger')->debug('Usage : ');
        $this->getDI()->get('logger')->debug('disturb.php "Tasks\\Manager" start --workflow="/path/to/workflow/config/file.json" [--name="workflowName"]');
    }

    protected function initWorker(array $paramHash)
    {
        $this->getDI()->get('logger')->debug(json_encode(func_get_args()));
        parent::initWorker($paramHash);
        $serviceFullName = $this->workflowConfig['servicesClassNameSpace'] . '\\' . ucFirst($this->workflowConfig['name']);
        // xxx Allow client to overwrite ?
        $this->workflowManagerService = new Services\WorkflowManager($paramHash['workflow']);
        $this->getDI()->get('logger')->debug('Loading ' . $serviceFullName);
        $this->service = new $serviceFullName();

        $this->topicName = Services\TopicService::getWorkflowManagerTopicName($this->workflowConfig['name']);
    }
    protected function processMessage(Dtos\Message $messageDto)
    {
        $this->getDI()->get('logger')->info('messageDto : ' . $messageDto);
        $status = $this->workflowManagerService->getStatus($messageDto->getContract());
        $this->getDI()->get('logger')->debug("Contract {$messageDto->getContract()} is '$status'");
        switch($messageDto->getType()) {
        case Dtos\Message::TYPE_WF_CTRL:
            switch($messageDto->getAction()) {
            case 'start':
                $this->workflowManagerService->init($messageDto->getContract());
                $this->runNextStep($messageDto->getContract());
                break;
            }
            break;
        case Dtos\Message::TYPE_STEP_ACK:
            $this->getDI()->get('logger')->debug("Step {$messageDto->getStep()} says {$messageDto->getResult()}");
            $stepResultHash = json_decode($messageDto->getResult(), true);

            $step = $this->workflowManagerService->finalizeStep($messageDto->getContract(), $messageDto->getStep(), $stepResultHash);
            $this->runNextStep($messageDto->getContract());
            break;
        default :
            $this->getDI()->get('logger')->error("ERR : Unknown message type : {$messageDto->getType()}");
        }
    }

    protected function runNextStep(string $workflowProcessId) {
        $stepTaskHashList = $this->workflowManagerService->getNextStepTaskList($workflowProcessId);
        // run through the next step(s)
        foreach ($stepTaskHashList as $stepTaskHash) {
            $stepCode = $stepTaskHash['name'];
            $stepInputList = $this->service->getStepInput($workflowProcessId, $stepCode);
            // run through the "job" to send to each step
            foreach ($stepInputList as $stepJobHash) {
                $messageHash = [
                    'id' => $workflowProcessId,
                    'type' => Dtos\Message::TYPE_STEP_CTRL,
                    'action' => 'start',
                    'payload' => $stepJobHash
                ];
                $stepMessageDto = new Dtos\Message(json_encode($messageHash));

                $this->sendMessage(
                    Services\TopicService::getWorkflowStepTopicName($stepCode, $workflowProcessId),
                    $stepMessageDto
                );
            }
        }
    }
}
