<?php
/*
 * This file is part of the Disturb package.
 *
 * (c) Matthieu Ventura <mventura@voyageprive.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Vpg\Disturb\Services;

class TopicService
{
    /**
     * @const String TOPIC_WORKFLOW_MANAGER_NAME
     */
    const TOPIC_WORKFLOW_MANAGER_NAME = 'disturb-@workflow_name@-manager';

    /**
     * @const String TOPIC_WORKFLOW_MANAGER_STEP_NAME
     */
    const TOPIC_WORKFLOW_MANAGER_STEP_NAME = 'disturb-@workflow_name@-@step_name@-step';

    /**
     * Generate formated topic name for workflow manager
     *
     * Example : disturb-LOAD_CONTRACT_28_FR-manager
     *
     * @param String $workflowName Workflow Name
     *
     * @throws \Exception Required parameters
     * @return String
     */
    public static function getWorkflowManagerTopicName (string $workflowName) : string
    {
        if (empty($workflowName)) {
            throw new \Exception('Parameters required - workflow name can not be null or empty');
        }

        return str_replace(
            ['@workflow_name@'],
            [$workflowName],
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
     * @throws \Exception Required parameters
     * @return String
     */
    public static function getWorkflowStepTopicName (string $stepName, string $workflowName) : string
    {
        if (empty($stepName) || empty($workflowName)) {
            throw new \Exception('Parameters required - step name or workflow name can not be null or empty');
        }

        return str_replace(
            ['@step_name@','@workflow_name@'],
            [$stepName, $workflowName],
            self::TOPIC_WORKFLOW_MANAGER_STEP_NAME
        );
    }
}

