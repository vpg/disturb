<?php
namespace Vpg\Disturb\Client;

use \Phalcon\Mvc\User\Component;
use \Vpg\Disturb\Message;

class DisturbStarter extends Component
{
    /**
     * Disturb Starter constructor
     *
     * @return void
     */
    public function __construct()
    {

    }

    /**
     * Start workflow by sending a message in related topic
     *
     * @param String $workflowId   Workflow id
     * @param Array  $payloadHash  List of params
     * @param String $brokers      broker list
     * @param String $topicName    topic name
     *
     */
    public static function start(string $workflowId, array $payloadHash, string $brokers, string $topicName)
    {
        $messageHash = [
            'id' => $workflowId,
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
}