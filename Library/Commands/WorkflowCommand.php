<?
/*
 * This file is part of the Disturb package.
 *
 * (c) Matthieu Ventura <mventura@voyageprive.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Vpg\Disturb\Commands;

use \Vpg\Disturb\Dtos;
use \Vpg\Disturb\Services\TopicService;

class WorkflowCommand
{
    /**
     * Start workflow by sending a message in related topic
     *
     * @param String $workflowName Workflow name
     * @param String $workflowId   Workflow id
     * @param Array  $payloadHash  List of params
     *
     */
    public static function start(string $workflowName, string $workflowId, array $payloadHash)
    {
        $brokers = 'localhost';

        $messageHash = [
            'id' => $workflowId,
            'type' => Dtos\Message::TYPE_WF_CTRL,
            'action' => 'start',
            'payload' => $payloadHash
        ];
        $stepMessageDto = new Dtos\Message(json_encode($messageHash));

        //send message with givens params
        $kafkaProducer = new \RdKafka\Producer();
        $kafkaProducer->addBrokers($brokers);
        $topicName = TopicService::getWorkflowManagerTopicName($workflowName);

        $kafkaTopic = $kafkaProducer->newTopic($topicName);
        $kafkaTopic->produce(RD_KAFKA_PARTITION_UA, 0, "$stepMessageDto");
    }
}
