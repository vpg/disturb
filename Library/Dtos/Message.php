<?
namespace Disturb\Dtos;

class Message implements \ArrayAccess
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

    private $rawHash = [];

    const WF_REQUIRED_PROP_HASH = ['id', 'type', 'action', 'payload'];

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
                throw new \Exception('Not able to parse message');
            }
            $this->rawHash = $rawHash;
        } else {
            throw new \Exception('Not supported raw message type');
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
            // xxx defined typed Exception
            throw new \Exception('Missing message Type');
        }
        switch ($this->rawHash['type']) {
            case self::TYPE_WF_CTRL:
                $matchPropList = array_intersect_key($this->rawHash, array_flip(self::WF_REQUIRED_PROP_HASH));
                $isValid = (count(self::WF_REQUIRED_PROP_HASH) == count($matchPropList));
            break;
            default:
                throw new \Exception('Validation of message type ' . $this->rawHash['type'] . ' is not implemented yet, please do');
        }
        if (!$isValid) {
            throw new \Exception('Missing properties for message ' . $this->rawHash['type'] . ' : ' .
                implode(',', self::WF_REQUIRED_PROP_HASH)
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

    public function offsetSet($offset, $value) {
        if (is_null($offset)) {
            $this->rawHash[] = $value;
        } else {
            $this->rawHash[$offset] = $value;
        }
    }

    public function offsetExists($offset) {
        return isset($this->rawHash[$offset]);
    }

    public function offsetUnset($offset) {
        unset($this->rawHash[$offset]);
    }

    public function offsetGet($offset) {
        return isset($this->rawHash[$offset]) ? $this->rawHash[$offset] : null;
    }
}
