<?php
namespace Disturb\Tasks;

use Phalcon\Cli\Task;
use Disturb\Dtos;

abstract class AbstractTask extends Task implements TaskInterface
{
    protected $topicPartitionNo = 0;

    protected $kafkaConf = null;
    protected $kafkaConsumer = null;
    protected $kafkaProducer = null;
    protected $kafkaTopicConf = null;
    protected $kafkaTopicConsumer = null;
    protected $kafkaTopicProducer = null;
    protected $kafkaTopicProducerHash = [];

    protected $topicName = '';
    protected $service = null;

    public function onConstruct() {
        echo PHP_EOL . '>' . __FUNCTION__ . ' : ' . json_encode(func_get_args());
        $brokers = 'localhost'; // xxx take it from conf
        // xxx put kafka\Conf in DI and config in a config file
        $this->kafkaConf = new \RdKafka\Conf();
        $this->kafkaConf->set('group.id', 'foo');
        // xxx put Consumer in a DI service
        $this->kafkaConsumer = new \RdKafka\Consumer($this->kafkaConf);
        $this->kafkaConsumer->setLogLevel(LOG_DEBUG);
        $this->kafkaConsumer->addBrokers($brokers);
        // xxx put kafka\TopicConf in DI and config in a config file
        $this->kafkaTopicConf = new \RdKafka\TopicConf();
        $this->kafkaTopicConf->set('offset.store.method', 'file');
        $this->kafkaTopicConf->set('auto.commit.interval.ms', 100);
        $this->kafkaTopicConf->set('offset.store.sync.interval.ms', 100);
        $this->kafkaTopicConf->set('offset.store.method', 'file');
        $this->kafkaTopicConf->set('offset.store.path', sys_get_temp_dir());
        $this->kafkaTopicConf->set('auto.offset.reset', 'smallest');

        $this->kafkaProducer = new \RdKafka\Producer();
        $this->kafkaProducer->addBrokers($brokers);
    }


    /**
     * @param array $params
     */
    public function startAction(array $paramHash)
    {
        $this->initAction($paramHash);

        echo PHP_EOL . '>' . __METHOD__ . ' : ' . json_encode(func_get_args());
        $this->kafkaTopicConsumer = $this->kafkaConsumer->newTopic($this->topicName, $this->kafkaTopicConf);
        $this->kafkaTopicConsumer->consumeStart($this->topicPartitionNo, RD_KAFKA_OFFSET_STORED);
        while (true) {
            $msg = $this->kafkaTopicConsumer->consume($this->topicPartitionNo, 100);
            // xxx q&d err handling
            if (!$msg ||  $msg->err) {
                if (!$msg) {
                    continue;
                }
                switch($msg->err) {
                case '-191': // no more msg
                    break;
                default:
                    echo "ERR : " . $msg->errstr() . PHP_EOL;
                }
                continue;
            }
            $msgDto = new Dtos\Message(json_decode($msg->payload, true));
            if (!isset($msgDto['type'])) {
                echo PHP_EOL. "ERR msg w/out type";
                continue;
            }
            if ($msgDto['type'] == Dtos\Message::TYPE_WF_MONITOR) {
                $this->processMonitoringMessage($msgDto);
                continue;
            }
            echo PHP_EOL . "RECEIVE msg on {$this->topicName} : $msgDto";
            $this->processMessage($msgDto);
        }
    }

    private function processMonitoringMessage(\Disturb\Dtos\Message $messageDto) {
        echo PHP_EOL . '>' . __METHOD__ . ' : ' . $messageDto;
        switch($messageDto['action']) {
        case Dtos\Message::ACTION_WF_MONITOR_PING:
            echo PHP_EOL . "PING receive from {$messageDto['from']}";
            $this->sendMessage($messageDto['from'], Dtos\Message::ACTION_WF_MONITOR_PONG);
            break;
        }
    }

    protected function sendMessage(string $topicName, Dtos\Message $message) {
        echo PHP_EOL . '>' . __METHOD__ . "($topicName, $message)";
        if (!isset($this->kafkaTopicProducerHash[$topicName])) {
            $this->kafkaTopicProducerHash[$topicName] = $this->kafkaProducer->newTopic($topicName);
        }
        $this->kafkaTopicProducerHash[$topicName]->produce(RD_KAFKA_PARTITION_UA, 0, $message);
    }
}
