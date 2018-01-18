<?php

namespace Vpg\Disturb\Core\Worker;

use \Phalcon\Cli\Task;

use Vpg\Disturb\Core;
use Vpg\Disturb\Message\MessageDto;
use Vpg\Disturb\Workflow\WorkflowException;
use Vpg\Disturb\Workflow\WorkflowConfigDtoFactory;

/**
 * Abstract Worker
 *
 * @package  Disturb\Core\Worker
 * @author   Jérome BOURGEAIS <jbourgeais@voyageprive.com>
 * @license  https://github.com/vpg/disturb/blob/master/LICENSE MIT Licence
 */
abstract class AbstractWorker extends Task implements WorkerInterface
{
    /**
     * Started worker status
     *
     * @const string STATUS_STARTED
     */
    const STATUS_STARTED = 'started';

    /**
     * Exited worker status
     *
     * @const string STATUS_EXITED
     */
    const STATUS_EXITED = 'exited';

    protected $taskOptionBaseList = [
        'workflow:', // required step code config file
        '?force',    // Optional force run even if lockfile exists
    ];

    protected $topicPartitionNo = 0;

    // xxx abstract MQ sys
    protected $kafkaConf = null;
    protected $kafkaConsumer = null;
    protected $kafkaProducer = null;
    protected $kafkaTopicConf = null;
    protected $kafkaTopicConsumer = null;
    protected $kafkaTopicProducerHash = [];

    /**
     * @var string $workerHostname Worker hostname
     */
    protected $topicName = '';

    /**
     * @var Object $service The client Class
     */
    protected $service = null;

    /**
     * @var \Phalcon\Config $workflowConfig Current workflow config
     */
    protected $workflowConfig;

    /**
     * @var string $workerHostname Worker hostname
     */
    protected $workerHostname = '';

    /**
     * @var string $workerCode Worker code
     */
    protected $workerCode = '';

    /**
     * @var array $paramHash Worker param
     */
    protected $paramHash = '';

    /**
     * Inits the current worker according to the given workflow config
     *  - Loads the config
     *  - Register Client biz classes
     *  - Init MQ sys
     *
     * @return void
     *
     * @throws WorkflowException
     */
    protected function initWorker()
    {
        $this->getDI()->get('logr')->debug(json_encode(func_get_args()));
        $this->workflowConfigDto = WorkflowConfigDtoFactory::get($this->paramHash['workflow']);
        $this->registerClientNS(
            $this->workflowConfigDto->getServicesClassNameSpace(),
            $this->workflowConfigDto->getServicesClassPath()
        );
        $this->workerHostname = php_uname('n');
        $this->workerCode = $this->workerHostname . '-' . $this->getWorkerCode($this->paramHash);
        $this->initMq();
    }

    /**
     * Parses and validates the given argv according to the worker options config
     * xxx should be moved in Disturb\Cli\Console class
     *
     * @param array $paramList The argv list
     *
     * @return array The parsed options hash
     */
    protected function parseOpt(array $paramList)
    {
        $this->getDI()->get('logr')->debug(json_encode(func_get_args()));
        $paramHash = Core\Cli\Console::parseLongOpt(join($paramList, ' '));
        foreach (array_merge($this->taskOptionBaseList, $this->taskOptionList) as $option) {
            $optionMatch = preg_match('/^(?<optionnal>\?)?(?<opt>\w+):?(?<val>\w+)?/', $option, $matchHash);
            // default values
            if (
                $optionMatch &&
                isset($matchHash['val']) &&
                empty($paramHash[$matchHash['opt']])
            ) {
                $this->getDI()->get('logr')->debug(
                    'Setting default value "' . $matchHash['val'] . '" for "' . $matchHash['opt'] . '"'
                );
                $paramHash[$matchHash['opt']] = $matchHash['val'];
            }
            // Required params
            if (
                $optionMatch &&
                empty($matchHash['optionnal']) &&
                !array_key_exists($matchHash['opt'], $paramHash)
            ) {
                $this->usage();
                throw new WorkerException('Wrong Usage : Missing params');
            }
        }
        return $paramHash;
    }

    /**
     * Start action
     *
     * @param array $paramList The argv list
     *
     * @return void
     */
    public final function startAction(array $paramList)
    {
        $this->getDI()->get('logr')->debug(json_encode(func_get_args()));
        $this->paramHash = $this->parseOpt($paramList);
        $this->lock();
        $this->initWorker();


        // xxx Factorize stdout/err support
        $this->getDI()->get('logr')->info(
            "Worker listening on \033[32m" .
            implode(',', $this->workflowConfigDto->getBrokerServerList()) .
            ":\033[32m" . $this->topicName . "\033[0m"
        );
        $this->kafkaConsumer->subscribe([$this->topicName]);
        while (true) {
            $processStartsAt = microtime(true);
            $msg = $this->kafkaConsumer->consume(10000);
            // xxx q&d err handling
            if (!$msg ||  $msg->err) {
                if (!$msg) {
                    continue;
                }
                switch ($msg->err) {
                    case '-191': // no more msg
                    case '-185': // timeout
                    break;
                    default:
                        $this->getDI()->get('logr')->error($msg->err . ' : ' . $msg->errstr());
                }
                continue;
            }
            $this->getDI()->get('logr')->info("RECEIVE msg on {$this->topicName} : $msg->payload");
            try {
                $msgDto = new MessageDto($msg->payload);
            } catch (\Exception $dtoException) {
                $this->getDI()->get('logr')->error(
                    "Invalid message : \033[31m" . $dtoException->getMessage() . "\033[0m"
                );
                continue;
            }
            try {
                $waitEndsAt = microtime(true);
                $waitExecTime = round(($waitEndsAt - $processStartsAt), 3);
                $this->getDI()->get('logr')->info("Idle during $waitExecTime secs");
                $processStartsAt = microtime(true);
                $this->processMessage($msgDto);
            } catch (\Exception $e) {
                $this->getDI()->get('logr')->error('Failed to process message : ' . $e->getMessage());
            }
            $processEndsAt = microtime(true);
            $processExecTime = round(($processEndsAt - $processStartsAt), 3);
            $this->getDI()->get('logr')->info("Message processed in $processExecTime secs");
        }
    }

    /**
     * Sends the given message to the specified topic
     *
     * @param string     $topicName Topic name on which send the message
     * @param MessageDto $message   The message to send
     *
     * @return void
     */
    protected function sendMessage(string $topicName, MessageDto $message)
    {
        $this->getDI()->get('logr')->debug("($topicName, $message)");
        if (!isset($this->kafkaTopicProducerHash[$topicName])) {
            $this->kafkaTopicProducerHash[$topicName] = $this->kafkaProducer->newTopic($topicName);
        }
        $this->kafkaTopicProducerHash[$topicName]->produce(RD_KAFKA_PARTITION_UA, 0, $message);
    }

    /**
     * Registers the "client" namespaces to make them auloadable
     *
     * @param string $clientServicesNamespace Absolute NS of the client logic service
     * @param string $clientServicesPath      Absolute file path to the service classes
     *
     * @return void
     */
    private function registerClientNS(string $clientServicesNamespace, string $clientServicesPath)
    {
        $this->getDI()->get('logr')->debug(json_encode(func_get_args()));
        $loader = $this->getDI()->getShared('loader');
        $loader->registerNamespaces([$clientServicesNamespace => $clientServicesPath], true);
        $loader->register();
    }

    /**
     * Init MQ
     *
     * @return void
     */
    private function initMq()
    {
        $this->getDI()->get('logr')->debug(json_encode(func_get_args()));
        $brokers = implode(',', $this->workflowConfigDto->getBrokerServerList());

        // xxx put kafka\TopicConf in DI and config in a config file
        $this->kafkaTopicConf = new \RdKafka\TopicConf();
        $this->kafkaTopicConf->set('auto.commit.interval.ms', 100);
        $this->kafkaTopicConf->set('offset.store.sync.interval.ms', 100);
        $this->kafkaTopicConf->set('offset.store.method', 'broker');
        $this->kafkaTopicConf->set('auto.offset.reset', 'smallest');

        // xxx put kafka\Conf in DI and config in a config file
        $this->kafkaConf = new \RdKafka\Conf();
        $this->kafkaConf->set('metadata.broker.list', $brokers);
        $group = $this->paramHash['step'] ?? 'manager';
        $this->kafkaConf->set('group.id', $group);
        $this->getDI()->get('logr')->info('Setting consumer group to ' . $group);
        $this->kafkaConf->setDefaultTopicConf($this->kafkaTopicConf);

        // xxx put Consumer in a DI service
        $this->kafkaConsumer = new \RdKafka\KafkaConsumer($this->kafkaConf);

        $this->kafkaProducer = new \RdKafka\Producer();
        $this->kafkaProducer->addBrokers($brokers);
    }

    /**
     * Returns the code of the current worker according to the worker given argv
     *
     * @param array $paramHash The argv list
     *
     * @return string worker code e.g. : disturb-step-computesomething-1
     */
    public static function getWorkerCode(array $paramHash)
    {
        $taskFullName = get_called_class();
        // xxx We will probably have to deal w/ the BU
        if (strpos($taskFullName, 'Manager')) {
            $workerName = 'disturb-manager';
        } else {
            $workerName = 'disturb-step-' . $paramHash['step'] . '-' . $paramHash['workerId'];
        }
        return $workerName;
    }
}
