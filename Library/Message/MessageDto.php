<?php

namespace Vpg\Disturb\Message;


/**
 * Class Message
 *
 * @package  Disturb\Message
 * @author   Jérome BOURGEAIS <jbourgeais@voyageprive.com>
 * @license  https://github.com/vpg/disturb/blob/master/LICENSE MIT Licence
 */
class MessageDto
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
    const MSG_RETURN_ERROR = 'ERROR';

    private $rawHash = [];

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
        if (is_array($rawMixed)) {
            $this->rawHash = $rawMixed;
        } elseif (is_string($rawMixed)) {
            if (!($rawHash = json_decode($rawMixed, true))) {
                // xxx defined typed Exception
                throw new InvalidMessageException('Not able to parse message');
            }
            $this->rawHash = $rawHash;
        } else {
            throw new InvalidMessageException('Not supported raw message type');
        }
        $this->validate();
    }

    /**
     * Validates the current message is valid
     *
     * @throws InvalidMessageException
     * @throws Exception
     *
     * @return void
     */
    public function validate()
    {
        $isValid = false;
        if (!isset($this->rawHash['type'])) {
            throw new InvalidMessageException('Missing message Type');
        }

        $propHashRequired = [];
        switch ($this->rawHash['type']) {
            case self::TYPE_WF_CTRL:
                $propHashRequired = self::WF_REQUIRED_PROP_HASH;
            break;
            case self::TYPE_STEP_CTRL:
                $propHashRequired = self::STEP_REQUIRED_PROP_HASH;
            break;
            case self::TYPE_STEP_ACK:
                $propHashRequired = self::STEP_ACK_REQUIRED_PROP_HASH;
            break;
            default:
                throw new InvalidMessageException(
                    'Validation of message type ' . $this->rawHash['type'] . ' is not implemented yet, please do'
                );
        }
        $matchPropList = array_intersect_key($this->rawHash, array_flip($propHashRequired));
        $isValid = (count($propHashRequired) == count($matchPropList));
        if (!$isValid) {
            throw new InvalidMessageException(
                'Missing properties for message ' . $this->rawHash['type'] . ' : ' .
                implode(',', $propHashRequired)
            );
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
        return $this->rawHash['jobId'] ?? '';
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
     * Get message sender
     *
     * @return string
     */
    public function getFrom(): string
    {
        return $this->rawHash['from'] ?? '';
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
     * Get contract id
     *
     * @return string
     */
    public function getContract(): string
    {
        return $this->rawHash['contract'] ?? '';
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
