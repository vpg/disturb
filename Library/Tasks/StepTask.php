<?php
namespace Disturb\Tasks;

use Phalcon\Cli\Task;

/**
 * Generic Step task
 * Dedicated to one step, given in argv with --step argument
 *
 *
 * @see \Disturb\Tasks\AbstractTask
 */
class StepTask extends \Disturb\Tasks\AbstractTask
{

    protected $taskOptionList = [
        'step:',     // required step code config file
    ];

    // xxx improve usage handling
    protected function usage()
    {
        echo PHP_EOL . 'Usage : ';
        echo PHP_EOL . 'disturb.php "Tasks\\Step" start --step="stepName" --workflow="/path/to/workflow/condfig/file.json" [--name="workflowName"]';
        echo PHP_EOL;
    }

    /**
     * Uses the business service related to the current step to process the given message
     *  - The message processing is fully delegated to the "client" service implementing the
     * \Disturb\Services\StepServiceInterface.php by calling the execute method
     *  - the process result (returned by the service) is sent back to the manager
     *
     * @param \Disturb\Dtos\Message $messageDto the message to process
     */
    protected function processMessage(\Disturb\Dtos\Message $messageDto)
    {
        echo PHP_EOL . '>' . __METHOD__ . " : $messageDto";
        $resultHash = $this->service->execute($messageDto);
        $msgDto = new \Disturb\Dtos\Message(
            json_encode([
                'contract' => $messageDto['contract'],
                'type' => \Disturb\Dtos\Message::TYPE_STEP_ACK,
                'step' => $this->topicName,
                'result' => json_encode($resultHash)
            ])
        );
        $this->sendMessage('disturb-' . $this->workflowConfig['name']  . '-manager', $msgDto);
    }

    /**
     * Specializes the current Step according to the given argvs
     *  - Sets the topic
     *  - Instanciates the "Client" service
     *
     * @param array $paramHash the parsed step task argv
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
