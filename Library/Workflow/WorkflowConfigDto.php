<?php

namespace Vpg\Disturb\Workflow;

use \Phalcon\Config;

use Vpg\Disturb\Core\Dto;


/**
 * Class Message
 *
 * @package  Disturb\Dtos
 * @author   Jérome BOURGEAIS <jbourgeais@voyageprive.com>
 * @license  https://github.com/vpg/disturb/blob/master/LICENSE MIT Licence
 */
class WorkflowConfigDto extends Dto\AbstractDto
{

    private $requiredProps = [
        'name',
        ['storage','config','host']
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
        $this->di->get('logger')->debug(json_encode(func_get_args()));
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
            throw new InvalidWorkflowConfigException('Missing properties :' . json_encode($missingPropList));
        }
    }

    /**
     * Returns the storage configuration
     *
     * @return array the storage host
     */
    public function getStorageConfig()
    {
        return $this->rawHash['storage']['config'] ?? [];
    }

    /**
     * Returns the storage host
     *
     * @return string the storage host
     */
    public function getStorageHost()
    {
        return $this->rawHash['storage']['config']['host'] ?? '';
    }

    /**
     * Returns the storage adapater
     *
     * @return string the storage host
     */
    public function getStorageAdapter()
    {
        return $this->rawHash['storage']['adapter'] ?? '';
    }

}
