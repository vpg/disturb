<?php

namespace Tests\Library\Topic;

use \phalcon\Config;
use \Vpg\Disturb\Topic\TopicService;
use \Vpg\Disturb\Topic\TopicException;

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
        $wfName = 'foo';
        $stepName = 'bar';
        define('DISTURB_TOPIC_PREFIX', 'test-');
        $topicName = TopicService::getWorkflowManagerTopicName($wfName);
        $this->assertEquals('test-disturb-foo-manager', $topicName);
        $topicName = TopicService::getWorkflowStepTopicName($stepName, $wfName);
        $this->assertEquals('test-disturb-foo-bar-step', $topicName);
    }
}
