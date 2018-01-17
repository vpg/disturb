<?php
namespace Vpg\Disturb\Client;

use \Phalcon\Mvc\User\Component;

use Vpg\Disturb\Context;
use Vpg\Disturb\Workflow\WorkflowConfigDto;

/**
 * Class Disturb Client
 * Context reader
 *
 * @package  Disturb\Client
 * @author   Jérome BOURGEAIS <jbourgeais@voyageprive.com>
 * @license  https://github.com/vpg/disturb/blob/master/LICENSE MIT Licence
 */
class Disturb extends Component
{
    private $workflowConfig;

    /**
     * Disturb Client constructor
     *
     * @param WorkflowConfigDto $workflowConfigDto config
     *
     * @return void
     */
    public function __construct(WorkflowConfigDto $workflowConfigDto)
    {
        $this->di->get('logr')->debug(json_encode(func_get_args()));
        $this->workflowConfig = $workflowConfigDto;
    }

    /**
     * Get a workflow representation dentified by $workflowProcessId
     *
     * @param string $workflowProcessId the workflow process id
     *
     * @return Context\ContextDto
     */
    public function getWorkflow(string $workflowProcessId)
    {
        $this->di->get('logr')->debug(json_encode(func_get_args()));
        $contextStorage = new Context\ContextStorageService($this->workflowConfig);
        return $contextStorage->get($workflowProcessId);
    }
}
