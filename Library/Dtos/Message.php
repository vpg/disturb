<?
namespace Vpg\Disturb\Dtos;

use Vpg\Disturb\Exceptions;

class Message
{
    const TYPE_STEP_CTRL = 'STEP-CTRL';
    const TYPE_STEP_ACK = 'STEP-ACK';
    const TYPE_WF_ACT = 'WF-ACTION';
    const TYPE_WF_CTRL = 'WF-CONTROL';
    const TYPE_WF_MONITOR = 'WF-MONITOR';

    const ACTION_WF_CTRL_START = 'WF-CONTROL-START';
    const ACTION_WF_CTRL_PAUSE = 'WF-CONTROL-PAUSE';
    const ACTION_WF_CTRL_RESUME = 'WF-CONTROL-RESUME';

    const ACTION_WF_MONITOR_PING = 'WF-MONITOR-PING';
    const ACTION_WF_MONITOR_PONG = 'WF-MONITOR-PONG';

    const MSG_RETURN_SUCCESS = 'SUCCESS';
    const MSG_RETURN_ERROR = 'ERROR';

    private $rawHash = [];

    const WF_REQUIRED_PROP_HASH = ['id', 'type', 'action', 'payload'];
    const STEP_REQUIRED_PROP_HASH = ['id', 'type', 'action', 'payload'];

    /**
     * Instanciates a new Message Dto according to the given data
     *
     * @param mixed $rawMixed could either be a string (json) or an array
     *
     * @return array The parsed options hash
     */
    public function __construct($rawMixed) {
        if (is_array($rawMixed)) {
            $this->rawHash = $rawMixed;
        } elseif (is_string($rawMixed)) {
            if (!($rawHash = json_decode($rawMixed, true))) {
                // xxx defined typed Exception
                throw new Exceptions\InvalidMessageException('Not able to parse message');
            }
            $this->rawHash = $rawHash;
        } else {
            throw new Exceptions\InvalidMessageException('Not supported raw message type');
        }
        $this->validate();
    }

    /**
     * Validates the current message is valid
     *
     * @throws \Exception in case of invalid message
     *
     * @return void
     */
    public function validate()
    {
        $isValid = false;
        if (!isset($this->rawHash['type'])) {
            throw new Exceptions\InvalidMessageException('Missing message Type');
        }

        $propHashRequired = [];
        switch ($this->rawHash['type']) {
            case self::TYPE_WF_CTRL:
                $propHashRequired = self::WF_REQUIRED_PROP_HASH;
            break;
            case self::TYPE_STEP_CTRL:
                $propHashRequired = self::STEP_REQUIRED_PROP_HASH;
            break;
            default:
                throw new \Exception(
                    'Validation of message type ' . $this->rawHash['type'] . ' is not implemented yet, please do'
                );
                throw new Exceptions\InvalidMessageException('Validation of message type ' . $this->rawHash['type'] . ' is not implemented yet, please do');
        }
        $matchPropList = array_intersect_key($this->rawHash, array_flip($propHashRequired));
        $isValid = (count($propHashRequired) == count($matchPropList));
        if (!$isValid) {
            throw new Exceptions\InvalidMessageException('Missing properties for message ' . $this->rawHash['type'] . ' : ' .
                implode(',', $propHashRequired)
            );
        }
    }

    public function getId(): string {
        return $this->rawHash['id'] ?? '';
    }

    public function getType(): string {
        return $this->rawHash['type'] ?? '';
    }

    public function __toString() {
        return json_encode($this->rawHash);
    }

    public function getPayload() : array {
        return !empty($this->rawHash['payload']) ? $this->rawHash['payload'] : [];
    }

    public function getFrom(): string {
        return $this->rawHash['from'] ?? '';
    }

    public function getAction(): string {
        return $this->rawHash['action'] ?? '';
    }

    public function getContract(): string {
        return $this->rawHash['contract'] ?? '';
    }

    public function getStep(): string {
        return $this->rawHash['step'] ?? '';
    }

    public function getResult(): string {
        return $this->rawHash['result'] ?? '';
    }

}
