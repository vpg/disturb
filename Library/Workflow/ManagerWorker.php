<?php

namespace Vpg\Disturb\Workflow;

use Vpg\Disturb\Topic;
use Vpg\Disturb\Message;
use Vpg\Disturb\Core;

/**
 * Manager task
 *
 * @package  Disturb\Workflow
 * @author   Jérome BOURGEAIS <jbourgeais@voyageprive.com>
 * @license  https://github.com/vpg/disturb/blob/master/LICENSE MIT Licence
 */
class ManagerWorker extends Core\AbstractWorker
{
    protected $taskOptionList = [
        '?name:'    // optional workflow name
    ];

    /**
     * Todo : improve usage handling
     *
     * @return void
     */
    protected function usage()
    {
        $this->getDI()->get('logr')->debug('Usage : ');
        $this->getDI()->get('logr')->debug(
            'disturb.php "Tasks\\Manager" start --workflow="/path/to/workflow/config/file.json" [--name="workflowName"]'
        );
    }

    /**
     * Init work with parameters
     *
     * @return void
     *
     * @throws WorkflowException
     */
    protected function initWorker()
    {
        $this->getDI()->get('logr')->debug(json_encode(func_get_args()));
        parent::initWorker();
        $this->workflowManagerService = new ManagerService($this->workflowConfigDto);
        $serviceFullName = $this->getServiceFullName();
        $this->getDI()->get('logr')->debug('Loading ' . $serviceFullName);
        $this->service = new $serviceFullName();

        $this->topicName = Topic\TopicService::getWorkflowManagerTopicName($this->workflowConfigDto->getWorkflowName());
    }

    /**
     * Get service full name
     *
     * @return string
     *
     * @throws WorkflowException
     */
    private function getServiceFullName() : string
    {
        $serviceFullName = $this->workflowConfigDto->getServicesClassNameSpace() . "\\" .
            ucFirst($this->workflowConfigDto->getWorkflowName()) . 'Manager';

        if (!class_exists($serviceFullName)) {
            throw new WorkflowException(
                $serviceFullName . ' manager class not found',
                WorkflowException::CODE_MANAGER_CLASS_NOT_FOUND
            );
        }

        return $serviceFullName;
    }

    /**
     * Process Dtos message
     *
     * @param Message\MessageDto $messageDto message DTO
     *
     * @throws WorkflowException
     *
     * @return void
     */
    protected function processMessage(Message\MessageDto $messageDto)
    {
        $this->getDI()->get('logr')->debug((string)$messageDto);
        switch ($messageDto->getType()) {
            case Message\MessageDto::TYPE_WF_CTRL:
                switch ($messageDto->getAction()) {
                    case Message\MessageDto::ACTION_WF_CTRL_START:
                        $this->getDI()->get('logr')->info("🚀 Starting workflow {$messageDto->getId()}");
                        try {
                            $this->workflowManagerService->init(
                                $messageDto->getId(),
                                $messageDto->getPayload(),
                                $this->workerHostname
                            );
                        } catch (WorkflowException $wfException) {
                            $this->getDI()->get('logr')->error(
                                "Failed to start workflow : {$wfException->getMessage()}"
                            );
                            return;
                        }
                        $this->runNextStep($messageDto->getId());
                    break;
                }
            break;
            case Message\MessageDto::TYPE_STEP_ACK:
                $this->getDI()->get('logr')->info(
                    "Step {$messageDto->getStepCode()} ack {$messageDto->getStepResultStatus()}"
                );
                $this->getDI()->get('logr')->debug(
                    "Step {$messageDto->getStepCode()} says " . json_encode($messageDto->getResult())
                );
                try {
                    $this->workflowManagerService->processStepJobResult(
                        $messageDto->getId(),
                        $messageDto->getStepCode(),
                        $messageDto->getJobId(),
                        $messageDto->getResult()
                    );
                } catch (WorkflowJobFinalizationException $workflowJobFinalizationException) {
                    $this->getDI()->get('logr')->warning($workflowJobFinalizationException->getMessage());
                    return;
                }

                $status = $this->workflowManagerService->getStatus($messageDto->getId());
                $this->getDI()->get('logr')->info("Id {$messageDto->getId()} is '$status'");
                if ($status == ManagerService::STATUS_FAILED) {
                    throw new WorkflowException("Id failed {$messageDto->getId()}");
                }

                $currentStepStatus = $this->workflowManagerService->getCurrentStepStatus($messageDto->getId());
                $this->getDI()->get('logr')->info(
                    "Workflow {$messageDto->getId()} - Current Step status : $currentStepStatus"
                );
                switch ($currentStepStatus) {
                    case ManagerService::STATUS_RUNNING:
                        // xxx check timeout
                    break;
                    case ManagerService::STATUS_SUCCESS:
                        if ($this->workflowManagerService->hasNextStep($messageDto->getId())) {
                            $this->runNextStep($messageDto->getId());
                        } else {
                            $this->workflowManagerService->finalize(
                                $messageDto->getId(),
                                ManagerService::STATUS_SUCCESS
                            );
                            $this->getDI()->get('logr')->info(
                                "🎉 Workflow {$messageDto->getId()} is now finished in success"
                            );
                        }
                    break;
                    case ManagerService::STATUS_FAILED:
                        $this->workflowManagerService->finalize(
                            $messageDto->getId(),
                            ManagerService::STATUS_FAILED
                        );
                        $this->getDI()->get('logr')->error("💥Workflow {$messageDto->getId()} has just failed");
                    break;
                    default:
                    throw new WorkflowException('Can\'t retrieve current step status');
                }
            break;
            default:
                $this->getDI()->get('logr')->error("ERR : Unknown message type : {$messageDto->getType()}");
        }
    }

    /**
     * Run next step with previous workflow process id
     *
     * @param string $workflowProcessId workflow process id
     *
     * @return void
     *
     * @throws \Exception
     */
    protected function runNextStep(string $workflowProcessId)
    {
        $this->getDI()->get('logr')->debug(json_encode(func_get_args()));
        $stepHashList = $this->workflowManagerService->getNextStepList($workflowProcessId);
        if (empty($stepHashList)) {
            $this->getDI()->get('logr')->info("No more step to run, WF ends");
            return;
        }
        $this->workflowManagerService->initNextStep($workflowProcessId);

        try {
            // run through the next step(s)
            foreach ($stepHashList as $stepHash) {
                $stepCode = $stepHash['name'];
                $stepInputList = $this->service->getStepInput($workflowProcessId, $stepCode);
                $this->getDI()->get('logr')->info("Nb job(s) to run for $stepCode : " . count($stepInputList));

                // run through the "job" to send to each step
                foreach ($stepInputList as $jobId => $stepJobHash) {
                    $this->workflowManagerService->registerStepJob($workflowProcessId, $stepCode, $jobId);
                    $messageHash = [
                        'id' => $workflowProcessId,
                        'type' => Message\MessageDto::TYPE_STEP_CTRL,
                        'jobId' => $jobId,
                        'stepCode' => $stepCode,
                        'action' => 'start',
                        'payload' => $stepJobHash
                    ];
                    $stepMessageDto = new Message\MessageDto(json_encode($messageHash));

                    $this->getDI()->get('logr')->info("Ask job #$jobId for $workflowProcessId : $stepCode");
                    $this->sendMessage(
                        Topic\TopicService::getWorkflowStepTopicName(
                            $stepCode,
                            $this->workflowConfigDto->getWorkflowName()
                        ),
                        $stepMessageDto
                    );
                }
            }
        } catch (\Exception $exception) {
            $this->getDI()->get('logr')->error($exception->getMessage());
            $this->workflowManagerService->finalize(
                $workflowProcessId,
                ManagerService::STATUS_FAILED,
                $exception->getMessage()
            );
        }

    }
}
