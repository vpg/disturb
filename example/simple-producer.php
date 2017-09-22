<?PHP
/**
 * Q&D Php kafka simple producer using rd kafka
 */

// q&d sanity check
if (empty($argv[1]) || empty($argv[2])) {
    die('usage : ./producer.php <msg> <topic> [<broker host>]');
}
$msg = $argv[1];
$topicName = $argv[2];
$brokers = @$argv[3]?:'localhost';

echo "Sending '$msg' to $brokers:$topicName" . PHP_EOL;
$kafkaProducer = new RdKafka\Producer();
$kafkaProducer->addBrokers($brokers);
$kafkaTopic = $kafkaProducer->newTopic($topicName);
$kafkaTopic->produce(RD_KAFKA_PARTITION_UA, 0, "$msg");
echo 'sent' . PHP_EOL;
