<?php

namespace Vpg\Disturb\Message;

use Vpg\Disturb\Core\Dto;

/**
 * Class Message
 *
 * @package  Disturb\Message
 * @author   Jérome BOURGEAIS <jbourgeais@voyageprive.com>
 * @license  https://github.com/vpg/disturb/blob/master/LICENSE MIT Licence
 */
class MessageDto extends Dto\AbstractDto
{
    const TYPE_STEP_CTRL = 'STEP-CTRL';
    const TYPE_STEP_ACK = 'STEP-ACK';
    const TYPE_WF_ACT = 'WF-ACTION';
    const TYPE_WF_CTRL = 'WF-CONTROL';
    const TYPE_WF_MONITOR = 'WF-MONITOR';

    /**
     * @const string ACTION_WF_CTRL_STOP Action workflow control to start the workflow
     */
    const ACTION_WF_CTRL_START = 'start';

    /**
     * @const string ACTION_WF_CTRL_STOP Action workflow control to stop the workflow
     */
    const ACTION_WF_CTRL_STOP = 'stop';

    const ACTION_WF_MONITOR_PING = 'WF-MONITOR-PING';
    const ACTION_WF_MONITOR_PONG = 'WF-MONITOR-PONG';

    const MSG_RETURN_SUCCESS = 'SUCCESS';
    const MSG_RETURN_FAILED = 'FAILED';

    const WF_REQUIRED_PROP_HASH = ['id', 'type', 'action', 'payload'];
    const STEP_REQUIRED_PROP_HASH = ['id', 'type', 'action', 'payload'];
    const STEP_ACK_REQUIRED_PROP_HASH = ['id', 'type', 'jobId', 'result'];

    /**
     * Instanciates a new Message Dto according to the given data
     *
     * @param mixed $rawMixed could either be a string (json) or an array
     *
     * @throws InvalidMessageException
     */
    public function __construct($rawMixed)
    {
        $this->di->get('logr')->debug(json_encode(func_get_args()));
        parent::__construct($rawMixed);
        $this->validate();
    }

    /**
     * Validates the current message is valid
     *
     * @throws InvalidMessageException
     *
     * @return void
     */
    public function validate()
    {
        if (!isset($this->rawHash['type'])) {
            throw new InvalidMessageException('Missing message Type');
        }
        $requiredPropHash = [];
        switch ($this->rawHash['type']) {
            case self::TYPE_WF_CTRL:
                $requiredPropHash = self::WF_REQUIRED_PROP_HASH;
            break;
            case self::TYPE_STEP_CTRL:
                $requiredPropHash = self::STEP_REQUIRED_PROP_HASH;
            break;
            case self::TYPE_STEP_ACK:
                $requiredPropHash = self::STEP_ACK_REQUIRED_PROP_HASH;
            break;
            default:
                throw new InvalidMessageException(
                    'Validation of message type ' . $this->rawHash['type'] . ' is not implemented yet, please do'
                );
        }
        $missingPropList = $this->getMissingPropertyList($requiredPropHash);
        if (!empty($missingPropList)) {
            throw new InvalidMessageException('Missing properties :' . json_encode($missingPropList));
        }
    }

    /**
     * Get message id
     *
     * @return string
     */
    public function getId(): string
    {
        return $this->rawHash['id'] ?? '';
    }

    /**
     * Get message job id
     *
     * @return string
     */
    public function getJobId(): string
    {
        return $this->rawHash['jobId'] ?? '0';
    }

    /**
     * Get message type
     *
     * @return string
     */
    public function getType(): string
    {
        return $this->rawHash['type'] ?? '';
    }

    /**
     * Get rawHash encoded
     *
     * @return string
     */
    public function __toString()
    {
        return json_encode($this->rawHash);
    }

    /**
     * Get message payload
     *
     * @return array
     */
    public function getPayload() : array
    {
        return !empty($this->rawHash['payload']) ? $this->rawHash['payload'] : [];
    }

    /**
     * Get message action
     *
     * @return string
     */
    public function getAction(): string
    {
        return $this->rawHash['action'] ?? '';
    }

    /**
     * Get step code
     *
     * @return string
     */
    public function getStepCode(): string
    {
        return $this->rawHash['stepCode'] ?? '';
    }

    /**
     * Get result
     *
     * @return array
     */
    public function getResult(): array
    {
        return $this->rawHash['result'] ?? [];
    }

    /**
     * Get step job result status
     *
     * @return string
     */
    public function getStepResultStatus(): string
    {
        return isset($this->rawHash['result']) ? $this->rawHash['result']['status'] ?? '' : '';
    }
}
