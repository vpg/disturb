<?php

namespace Vpg\Disturb\Topic;

use \Phalcon\DI;

/**
 * Class TopicService
 *
 * @package  Disturb\Topic
 * @author   Matthieu VENTURA <mventura@voyageprive.com>
 * @license  https://github.com/vpg/disturb/blob/master/LICENSE MIT Licence
 */
class TopicService
{
    /**
     * Topic workflow manager name
     *
     * @const String TOPIC_WORKFLOW_MANAGER_NAME
     */
    const TOPIC_WORKFLOW_MANAGER_NAME = '@prefix@disturb-@workflow_name@-manager';

    /**
     * Topic workflow manager step name
     *
     * @const String TOPIC_WORKFLOW_MANAGER_STEP_NAME
     */
    const TOPIC_WORKFLOW_MANAGER_STEP_NAME = '@prefix@disturb-@workflow_name@-@step_name@-step';

    /**
     * Generate formated topic name for workflow manager
     *
     * Example : disturb-LOAD_CONTRACT_28_FR-manager
     *
     * @param String $workflowName Workflow Name
     *
     * @throws Core\Exception Required parameters
     * @return String
     */
    public static function getWorkflowManagerTopicName(string $workflowName) : string
    {
        if (empty($workflowName)) {
            throw new TopicException('Parameters required - workflow name can not be null or empty');
        }

        return str_replace(
            ['@prefix@', '@workflow_name@'],
            [self::getTopicPrefix(), $workflowName],
            self::TOPIC_WORKFLOW_MANAGER_NAME
        );
    }

    /**
     * Generate formated topic name for step manager
     *
     * Example : disturb-step0-LOAD_CONTRACT_28_FR-step
     *
     * @param String $stepName     Step Name
     * @param String $workflowName Workflow Name
     *
     * @throws Core\Exception Required parameters
     * @return String
     */
    public static function getWorkflowStepTopicName(string $stepName, string $workflowName) : string
    {
        if (empty($stepName) || empty($workflowName)) {
            throw new TopicException('Parameters required - step name or workflow name can not be null or empty');
        }

        return str_replace(
            ['@prefix@', '@step_name@','@workflow_name@'],
            [self::getTopicPrefix() , $stepName, $workflowName],
            self::TOPIC_WORKFLOW_MANAGER_STEP_NAME
        );
    }

    /**
     * Returns the topic prefix
     *
     * @return String
     */
    public static function getTopicPrefix() : string
    {
        return !empty(
            DI::getDefault()->get('disturb-config')['topicPrefix']
        ) ? trim(DI::getDefault()->get('disturb-config')['topicPrefix']) : '';
    }

}
