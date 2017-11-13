<?php
namespace Vpg\Disturb\Tasks;

use Phalcon\Cli\Task;
use \Vpg\Disturb\Exceptions\WorkflowException;
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

    protected function initWorker()
    {
        $this->getDI()->get('logger')->debug(json_encode(func_get_args()));
        parent::initWorker();
        $serviceFullName = $this->workflowConfig['servicesClassNameSpace'] . "\\" .
            ucFirst($this->workflowConfig['name']) . 'Manager';
        // xxx Allow client to overwrite ?
        $this->workflowManagerService = new Services\WorkflowManager($this->paramHash['workflow']);
        $this->getDI()->get('logger')->debug('Loading ' . $serviceFullName);
        $this->service = new $serviceFullName();

        $this->topicName = Services\TopicService::getWorkflowManagerTopicName($this->workflowConfig['name']);
    }

    protected function processMessage(Dtos\Message $messageDto)
    {
        $this->getDI()->get('logger')->info('messageDto : ' . $messageDto);
        switch($messageDto->getType()) {
            case Dtos\Message::TYPE_WF_CTRL:
                switch($messageDto->getAction()) {
                    case 'start':
                        $this->workflowManagerService->init($messageDto->getId());
                        $this->runNextStep($messageDto->getId());
                    break;
                }
                break;
            case Dtos\Message::TYPE_STEP_ACK:
                $this->getDI()->get('logger')->debug("Step {$messageDto->getStepCode()} says {$messageDto->getResult()}");
                $stepResultHash = json_decode($messageDto->getResult(), true);
                $this->workflowManagerService->processStepJobResult(
                    $messageDto->getId(),
                    $messageDto->getStepCode(),
                    $messageDto->getJobId(),
                    $stepResultHash
                );

                $status = $this->workflowManagerService->getStatus($messageDto->getId());
                $this->getDI()->get('logger')->debug("Id {$messageDto->getId()} is '$status'");
                if ($status == Services\WorkflowManager::STATUS_FAILED) {
                    throw new WorkflowException("Id failed {$messageDto->getId()}");
                }

                switch($this->workflowManagerService->getCurrentStepStatus($messageDto->getId())) {
                    case Services\WorkflowManager::STATUS_RUNNING:
                        // xxx check timeout
                        break;
                    case Services\WorkflowManager::STATUS_SUCCESS:
                        $this->runNextStep($messageDto->getId());
                        break;
                    case Services\WorkflowManager::STATUS_FAILED:
                        $this->workflowManagerService->setStatus(
                            $messageDto->getId(),
                            Services\WorkflowManager::STATUS_FAILED
                        );
                        break;
                    default:
                        throw new WorkflowException('Can\'t retrieve current step status');
                }
                break;
            default:
                $this->getDI()->get('logger')->error("ERR : Unknown message type : {$messageDto->getType()}");
        }
    }

    protected function runNextStep(string $workflowProcessId)
    {

        $stepHashList = $this->workflowManagerService->getNextStepList($workflowProcessId);
        if (empty($stepHashList)) {
            $this->getDI()->get('logger')->info("No more step to run, WF ends");
            var_dump($this->workflowManagerService->getContext($workflowProcessId));
        }
        $this->workflowManagerService->initNextStep($workflowProcessId);

        // run through the next step(s)
        foreach ($stepHashList as $stepHash) {
            $stepCode = $stepHash['name'];
            $stepInputList = $this->service->getStepInput($workflowProcessId, $stepCode);
            // run through the "job" to send to each step
            foreach ($stepInputList as $jobId => $stepJobHash) {
                $this->workflowManagerService->registerStepJob($workflowProcessId, $stepCode, $jobId);
                $messageHash = [
                    'id' => $workflowProcessId,
                    'type' => Dtos\Message::TYPE_STEP_CTRL,
                    'jobId' => $jobId,
                    'stepCode' => $stepCode,
                    'action' => 'start',
                    'payload' => $stepJobHash
                ];
                $stepMessageDto = new Dtos\Message(json_encode($messageHash));

                $this->sendMessage(
                    Services\TopicService::getWorkflowStepTopicName($stepCode, $this->workflowConfig['name']),
                    $stepMessageDto
                );
            }
        }
    }
}
