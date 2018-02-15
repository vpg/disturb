<?php
namespace Vpg\Disturb\Client;

use \Phalcon\Mvc\User\Component;
use \Vpg\Disturb\Message;
use \Vpg\Disturb\Workflow\WorkflowConfigDtoFactory;
use Vpg\Disturb\Context;

include realpath(__DIR__ . '/../../bin/configCommand.php');

/**
 * Class Disturb Client Command
 *
 * @package  Disturb\Client
 * @author   Maxime BRENGUIER <mbrenguier@voyageprive.com>
 * @license  https://github.com/vpg/disturb/blob/master/LICENSE MIT Licence
 */
class Command extends Component
{
    /**
     * Start workflow by sending a message in related topic
     *
     * @param String $workflowProcessId Workflow id
     * @param Array  $payloadHash       List of params
     * @param String $brokers           broker list
     * @param String $topicName         topic name
     *
     * @return void
     */
    public static function start(string $workflowProcessId, array $payloadHash, string $brokers, string $topicName)
    {
        $messageHash = [
            'id' => $workflowProcessId,
            'type' => Message\MessageDto::TYPE_WF_CTRL,
            'action' => 'start',
            'payload' => $payloadHash
        ];
        //send message with givens params
        $kafkaProducer = new \RdKafka\Producer();
        $kafkaProducer->addBrokers($brokers);
        $kafkaTopic = $kafkaProducer->newTopic($topicName);
        $kafkaTopic->produce(RD_KAFKA_PARTITION_UA, 0, json_encode($messageHash));
    }

    /**
     * Get status for a specified workflow process id
     *
     * @param string $workflowProcessId      workflow process id
     * @param string $workflowConfigFilePath workflow config file path
     *
     * @return string
     */
    public static function getStatus(string $workflowProcessId, string $workflowConfigFilePath)
    {
        $workflowConfigDto = WorkflowConfigDtoFactory::get($workflowConfigFilePath);
        $contextStorage = new Context\ContextStorageService($workflowConfigDto);
        return $contextStorage->get($workflowProcessId)->getWorkflowStatus();
    }
}