<?php
namespace Vpg\Disturb\Tasks;

use \Phalcon\Cli\Task;

use \Vpg\Disturb\Dtos;
use \Vpg\Disturb\Tasks\AbstractTask as AbstractTask;

/**
 * Generic Step task
 * Dedicated to one step, given in argv with --step argument
 *
 *
 * @see \Disturb\Tasks\AbstractTask
 */
class StepTask extends AbstractTask
{

    protected $taskOptionList = [
        'step:',     // required step code config file
    ];

    // xxx improve usage handling
    protected function usage()
    {
        echo PHP_EOL . 'Usage : ';
        echo PHP_EOL . 'disturb.php "Tasks\\Step" start --step="stepName" --workflow="/path/to/workflow/config/file.json" [--name="workflowName"]';
        echo PHP_EOL;
    }

    /**
     * Uses the business service related to the current step to process the given message
     *  - The message processing is fully delegated to the "client" service implementing the
     * \Disturb\Services\StepServiceInterface.php by calling the execute method
     *  - the process result (returned by the service) is sent back to the manager
     *
     * @param Vpg\Disturb\Dtos\Message $messageDto the message to process
     *
     * @return void
     */
    protected function processMessage(Dtos\Message $messageDto)
    {
        echo PHP_EOL . '>' . __METHOD__ . " : $messageDto";
        $resultHash = $this->service->execute($messageDto->getPayload());
        $msgDto = new Dtos\Message(
            [
                'id' => $messageDto->getId(),
                'type' => Dtos\Message::TYPE_STEP_ACK,
                'stepCode' => $messageDto->getStepCode(),
                'jobId' => $messageDto->getJobId(),
                'result' => json_encode($resultHash)
            ]
        );
        $this->sendMessage('disturb-' . $this->workflowConfig['name']  . '-manager', $msgDto);
    }

    /**
     * Specializes the current Step according to the given argvs
     *  - Sets the topic
     *  - Instanciates the "Client" service
     *
     * @param array $paramHash the parsed step task argv
     *
     * @return void
     */
    protected function initWorker(array $paramHash)
    {
        echo PHP_EOL . '>' . __METHOD__ . ' : ' . json_encode(func_get_args());
        parent::initWorker($paramHash);
        $serviceFullName = $this->workflowConfig['servicesClassNameSpace'] . '\\' . ucFirst($paramHash['step']);
        $this->service = new $serviceFullName($paramHash['workflow']);
        $this->topicName = 'disturb-' . $paramHash['step'] . '-step';
    }
}
