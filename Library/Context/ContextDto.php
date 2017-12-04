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
        ['workflow', 'steps']
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
    public function getInitialPayload()
    {
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
    public function getStepResultData()
    {
        $allStepResultHash = [];
        foreach ($this->rawHash['workflow']['steps'] as $stepHash) {
            $stepResultHash = array_column($stepHash['jobList'], 'result');
            if (empty(array_filter($stepResultHash))) {
                continue;
            }
            $allStepResultHash[$stepHash['name']] = array_column($stepHash['jobList'], 'result');
        }
        return $allStepResultHash;
    }
}
