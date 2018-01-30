<?php

namespace Tests\Library\Topic;

use \phalcon\Config;
use \Vpg\Disturb\Topic\TopicService;
use \Vpg\Disturb\Topic\TopicException;
use \Vpg\Disturb\Workflow;

/**
 * Workflow Manager Service test class
 *
 * @author  JEROME BOURGEAIS <jbourgeais@voyageprive.com>
 */
class TopicServiceTest extends \Tests\DisturbUnitTestCase
{
    /**
     * Test manager topic name()
     *
     * @return void
     */
    public function testGetWorkflowTopicNameWithoutPrefix()
    {
        $wfName = 'foo';
        $stepName = 'bar';
        $this->expectException(TopicException::class);
        $topicName = TopicService::getWorkflowManagerTopicName('');
        $topicName = TopicService::getWorkflowManagerTopicName($wfName);
        $this->assertEquals('disturb-foo-manager', $topicName);
        $this->expectException(TopicException::class);
        $topicName = TopicService::getWorkflowStepTopicName('', '');
        $topicName = TopicService::getWorkflowStepTopicName($stepName, $wfName);
        $this->assertEquals('disturb-foo-bar-step', $topicName);
    }

    /**
     * Test manager topic name()
     *
     * @return void
     */
    public function testGetWorkflowTopicNameWithPrefix()
    {
        $configFilepath = realpath(__DIR__ . '/../../Config/serie.json');
        $workerParamHash = [
            "--workflow=$configFilepath",
            "--topicPrefix=test-"
        ];
        $managerWorker = new Workflow\ManagerWorker();
        $managerWorkerReflection = new \ReflectionClass($managerWorker);
        $parseOtpF = $managerWorkerReflection->getMethod('parseOpt');
        $parseOtpF->setAccessible(true);
        $parsedOptHash = $parseOtpF->invokeArgs($managerWorker, [$workerParamHash]);

        $wfName = 'foo';
        $stepName = 'bar';
        $topicName = TopicService::getWorkflowManagerTopicName($wfName);
        $this->assertEquals('test-disturb-foo-manager', $topicName);
        $topicName = TopicService::getWorkflowStepTopicName($stepName, $wfName);
        $this->assertEquals('test-disturb-foo-bar-step', $topicName);
    }
}
