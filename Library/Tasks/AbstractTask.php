<?php

namespace Vpg\Disturb\Tasks;

use \Phalcon\Cli\Task;
use \Phalcon\Loader;
use \Phalcon\Config\Adapter\Json;

use \Vpg\Disturb\Dtos;
use \Vpg\Disturb\Cli;
use \Vpg\Disturb\Exceptions;

/**
 * Abstract task
 *
 * @category Tasks
 * @package  Disturb\Tasks
 * @author   Jérome BOURGEAIS <jbourgeais@voyageprive.com>
 * @license  https://github.com/vpg/disturb/blob/master/LICENSE MIT Licence
 * @link     http://example.com/my/bar Documentation of Foo.
 */
abstract class AbstractTask extends Task implements TaskInterface
{
    protected $taskOptionBaseList = [
        'workflow:', // required step code config file
        '?force',    // Optional step code config file
    ];

    protected $topicPartitionNo = 0;

    // xxx abstract MQ sys
    protected $kafkaConf = null;
    protected $kafkaConsumer = null;
    protected $kafkaProducer = null;
    protected $kafkaTopicConf = null;
    protected $kafkaTopicConsumer = null;
    protected $kafkaTopicProducerHash = [];

    protected $topicName = '';
    protected $service = null;

    protected $workflowConfig;


    /**
     * Inits the current worker according to the given workflow config
     *  - Loads the config
     *  - Register Client biz classes
     *  - Init MQ sys
     *
     * @return void
     */
    protected function initWorker() {
        $this->getDI()->get('logger')->debug(json_encode(func_get_args()));
        // xxx check if file exists, throw exc on err
        $this->workflowConfig = new Json($this->paramHash['workflow']);
        $this->registerClientNS(
            $this->workflowConfig['servicesClassNameSpace'],
            $this->workflowConfig['servicesClassPath']
        );
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
    private function parseOpt(array $paramList)
    {
        $this->getDI()->get('logger')->debug(json_encode(func_get_args()));
        $paramHash = Cli\Console::parseLongOpt(join($paramList, ' '));
        foreach(array_merge($this->taskOptionBaseList, $this->taskOptionList) as $option) {
            $optionMatch = preg_match('/^(?<optionnal>\?)?(?<opt>\w+):?(?<val>\w+)?/', $option, $matchHash);
            // default values
            if (
                $optionMatch &&
                isset($matchHash['val']) &&
                empty($paramHash[$matchHash['opt']])
            ) {
                $this->getDI()->get('logger')->debug(
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
                exit(1);
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
        $this->getDI()->get('logger')->debug(json_encode(func_get_args()));
        $this->paramHash = $this->parseOpt($paramList);
        $this->lock();
        $this->initWorker();

        $this->kafkaTopicConsumer = $this->kafkaConsumer->newTopic($this->topicName, $this->kafkaTopicConf);
        $this->kafkaTopicConsumer->consumeStart($this->topicPartitionNo, RD_KAFKA_OFFSET_STORED);
        // xxx Factorize stdout/err support
        $this->getDI()->get('logger')->info("Worker listening on \033[32m" .
            implode(',', $this->workflowConfig['brokerServerList']->toArray()) .
            ":\033[32m" . $this->topicName . "\033[0m");
        while (true) {
            $msg = $this->kafkaTopicConsumer->consume($this->topicPartitionNo, 100);
            // xxx q&d err handling
            if (!$msg ||  $msg->err) {
                if (!$msg) {
                    continue;
                }
                switch ($msg->err) {
                    case '-191': // no more msg
                    break;
                    default:
                        $this->getDI()->get('logger')->error($msg->errstr());
                }
                continue;
            }
            $this->getDI()->get('logger')->info("RECEIVE msg on {$this->topicName} : $msg->payload");
            try {
                $msgDto = new Dtos\Message($msg->payload);
            } catch (\Exception $dtoException) {
                $this->getDI()->get('logger')->error(
                    "Invalid message : \033[31m" . $dtoException->getMessage() . "\033[0m"
                );
                continue;
            }
            if ($msgDto->getType() == Dtos\Message::TYPE_WF_MONITOR) {
                $this->processMonitoringMessage($msgDto);
                continue;
            }
            $this->processMessage($msgDto);
        }
    }

    /**
     * Process monitoring message
     *
     * @param Vpg|Dtos|Message $messageDto Dtos message
     *
     * @return void
     */
    private function processMonitoringMessage(Dtos\Message $messageDto)
    {
        $this->getDI()->get('logger')->debug($messageDto);
        switch ($messageDto->getAction()) {
            case Dtos\Message::ACTION_WF_MONITOR_PING:
                $this->getDI()->get('logger')->debug("PING receive from {$messageDto->getFrom()}");
                $this->sendMessage($messageDto->getFrom(), Dtos\Message::ACTION_WF_MONITOR_PONG);
            break;
        }
    }

    /**
     * Sends the given message to the specified topic
     *
     * @param string       $topicName Topic name on which send the message
     * @param Dtos\Message $message   The message to send
     *
     * @return void
     */
    protected function sendMessage(string $topicName, Dtos\Message $message)
    {
        $this->getDI()->get('logger')->debug("($topicName, $message)");
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
        $this->getDI()->get('logger')->debug(json_encode(func_get_args()));
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
        $this->getDI()->get('logger')->debug(json_encode(func_get_args()));
        $brokers = implode(',', $this->workflowConfig['brokerServerList']->toArray());
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
     * Sets a lock for the current process according to its params
     *
     * @throws Exceptions\WorkflowException if lock exists or perm issue
     * @return void
     */
    private function lock() {
        $this->getDI()->get('logger')->debug(json_encode(func_get_args()));
        $pid = getMyPid();
        $lockFileName = $this->getLockFilePath();
        if (file_exists($lockFileName) && !isset($this->paramHash['force'])) {
            throw new Exceptions\WorkflowException('Failed to lock process, already running or zombie');
        }
        if(!file_put_contents($lockFileName, $pid, LOCK_EX)) {
            throw new Exceptions\WorkflowException('Failed to lock process : failed to write file ' . $lockFileName);
        }
    }

    /**
     * Returns a lock file path related to the current process
     *
     * @return string a log file path. e.g. : /var/run/disturb-step-checkInfraGroupLodging-0.pid
     */
    private function getLockFilePath() {
        $this->getDI()->get('logger')->debug(json_encode(func_get_args()));
        $lockDirPath = '/var/run/';
        $taskFullName = get_called_class();
        // xxx We will probably have to deal w/ the BU
        if (strpos($taskFullName, 'Manager')) {
            $lockFileName = 'disturb-manager';
        }
        else {
            $lockFileName = 'disturb-step-' . $this->paramHash['step'] . '-' . $this->paramHash['workerId'];
        }
        return $lockDirPath . $lockFileName . '.pid';
    }
}
