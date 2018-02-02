<?php

namespace Vpg\Disturb\Context;

use \Phalcon\Config;

use Vpg\Disturb\Core\Dto;


/**
 * Class ContextDto
 *
 * @package  Disturb\Dtos
 * @author   Jérome BOURGEAIS <jbourgeais@voyageprive.com>
 * @license  https://github.com/vpg/disturb/blob/master/LICENSE MIT Licence
 */
class ContextDto extends Dto\AbstractDto
{

    private $requiredProps = [
        'initialPayload',
        'status',
        'steps'
    ];

    /**
     * Instanciates a new workflow config according to the given file path
     *
     * @param string $mixed the file path of the json config
     *
     * @throws InvalidConfigException
     */
    public function __construct($mixed)
    {
        $this->di->get('logr')->debug(json_encode(func_get_args()));
        parent::__construct($mixed);
        $this->validate();
    }

    /**
     * Validates the current config
     *
     * @throws InvalidWorkflowConfigException
     *
     * @return void
     */
    public function validate()
    {
        $missingPropList = $this->getMissingPropertyList($this->requiredProps);
        if (!empty($missingPropList)) {
            throw new ContextStorageException('Missing properties :' . json_encode($missingPropList));
        }
    }

    /**
     * Returns the initial payload of the current workflow context
     *
     * @return array the initial payload
     */
    public function getInitialPayload() : array
    {
        $this->di->get('logr')->debug(json_encode(func_get_args()));
        return $this->rawHash['initialPayload'] ?? [];
    }

    /**
     * Returns the results of all steps
     *
     * @return array the result of all processed steps
     *               [
     *                  'stepName' => ['foo' => 'bar'],
     *               ]
     */
    public function getStepResultData() : array
    {
        $this->di->get('logr')->debug(json_encode(func_get_args()));
        $allStepResultHash = [];
        foreach ($this->rawHash['steps'] as $stepHash) {
            // if // steps
            if (!(array_keys($stepHash) !== array_keys(array_keys($stepHash)))) {
                foreach ($stepHash as $parallelizedStepHash) {
                    if (!isset($parallelizedStepHash['jobList'])) {
                        continue;
                    }
                    $stepResultHash = array_column($parallelizedStepHash['jobList'], 'result');
                    if (!$stepResultHash || empty(array_filter($stepResultHash))) {
                        continue;
                    }
                    $allStepResultHash[$parallelizedStepHash['name']] = array_column(
                        $parallelizedStepHash['jobList'],
                        'result'
                    );
                }

            } else {
                if (!isset($stepHash['jobList'])) {
                    continue;
                }
                $stepResultHash = array_column($stepHash['jobList'], 'result');
                if (!$stepResultHash || empty(array_filter($stepResultHash))) {
                    continue;
                }
                $allStepResultHash[$stepHash['name']] = array_column($stepHash['jobList'], 'result');
            }
        }
        return $allStepResultHash;
    }

    /**
     * Returns the status of the current workflow context
     *
     * @return string the current status
     */
    public function getWorkflowStatus() : string
    {
        $this->di->get('logr')->debug(json_encode(func_get_args()));
        return $this->rawHash['status'] ?? '';
    }

    /**
     * Returns the info of the current workflow context status
     *
     * @return string the current info
     */
    public function getWorkflowInfo() : string
    {
        $this->di->get('logr')->debug(json_encode(func_get_args()));
        return $this->rawHash['info'] ?? '';
    }

    /**
     * Returns the step position of the current workflow context
     *
     * @return int the current step position
     */
    public function getWorkflowCurrentPosition() : int
    {
        $this->di->get('logr')->debug(json_encode(func_get_args()));
        return $this->rawHash['currentStepPos'] ?? -1;
    }

    /**
     * Returns the step list of the current workflow context
     *
     * @return array the current step list
     */
    public function getWorkflowStepList() : array
    {
        $this->di->get('logr')->debug(json_encode(func_get_args()));
        return $this->rawHash['steps'] ?? [];
    }

    /**
     * Returns the step list of the current workflow context for the given position
     *
     * @param int $positionNo the postion
     *
     * @return array the step list
     */
    public function getWorkflowStepListByPosition(int $positionNo) : array
    {
        $this->di->get('logr')->debug(json_encode(func_get_args()));
        return $this->rawHash['steps'][$positionNo] ?? [];
    }

    /**
     * Returns the step node related to the given code
     *
     * @param string $code the step code
     *
     * @return array the step node
     *               [
     *                  'stepName' => ['foo' => 'bar'],
     *               ]
     */
    public function getStep(string $code) : array
    {
        $this->di->get('logr')->debug(json_encode(func_get_args()));
        foreach ($this->rawHash['steps'] as $stepHash) {
            // if // steps
            if (!(array_keys($stepHash) !== array_keys(array_keys($stepHash)))) {
                foreach ($stepHash as $parallelizedStepHash) {
                    if ($parallelizedStepHash['name'] == $code) {
                        return $parallelizedStepHash;
                    }
                }
            } else {
                if ($stepHash['name'] == $code) {
                    return $stepHash;
                }
            }
        }
        return [];
    }
}
