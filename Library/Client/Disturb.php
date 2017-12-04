<?php
namespace Vpg\Disturb\Client;

use \Phalcon\Mvc\User\Component;

use Vpg\Disturb\Context;

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
    /**
     * Disturb Client constructor
     *
     * @return void
     */
    public function __construct()
    {
        $this->di->get('logr')->debug(json_encode(func_get_args()));
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
        $contextStorage = new Context\ContextStorageService($this->di->get('disturb-config')->get('workflowConfigFilePath'));
        return $contextStorage->get($workflowProcessId);
    }
}
