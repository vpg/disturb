<?php

namespace Tests\Library\Topic;

use \phalcon\Config;
use \Vpg\Disturb\Topic\TopicService;

/**
 * Workflow Manager Service test class
 *
 * @author  JEROME BOURGEAIS <jbourgeais@voyageprive.com>
 */
class TopicServiceTest extends \Tests\DisturbUnitTestCase
{
    /**
     * Setup
     *
     * @return void
     */
    public function setUp()
    {
        parent::setUp();
    }

    /**
     * Test manager topic name()
     *
     * @return void
     */
    public function testGetWorkflowTopicNameWithoiutPrefix()
    {
        $wfName = 'foo';
        $stepName = 'bar';
        $topicName = TopicService::getWorkflowManagerTopicName($wfName);
        $this->assertEquals('disturb-foo-manager', $topicName);
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
