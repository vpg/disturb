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
$nb = @$argv[4]?:1;
$prefix = @$argv[5]?:'A';

echo "Sending '$msg' to $brokers:$topicName" . PHP_EOL;
$kafkaProducer = new RdKafka\Producer();
$kafkaProducer->addBrokers($brokers);
$kafkaTopic = $kafkaProducer->newTopic($topicName);
$msgHash = json_decode($msg, true);
for($i=0; $i<$nb; $i++) {
    $msgHash['payload']['i'] = $prefix.$i;
    $kafkaTopic->produce(RD_KAFKA_PARTITION_UA, 0, json_encode($msgHash));
    echo 'sent' . PHP_EOL;
    sleep(1);
}
