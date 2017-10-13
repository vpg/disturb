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

    private $WF_REQUIRED_PROP_HASH = ['id', 'type', 'action', 'payload'];

    public function __construct(string $rawPayload) {
        if (!($rawHash = json_decode($rawPayload, true))){
            // xxx defined typed Exception
            throw new \Exception('Not able to parse message');
        }
        $this->rawHash = $rawHash;
        $this->validate();
    }

    public function getId(): string {
        return $this->rawHash['id'];
    }
    public function getType(): string {
        return $this->rawHash['type'];
    }

    public function validate()
    {
        if (!isset($this->rawHash['type'])) {
            // xxx defined typed Exception
            throw new \Exception('Missing message Type');
        }
        $isValid = false;
        switch ($this->rawHash['type']) {
            case self::TYPE_WF_CTRL:
                $matchPropList = array_intersect_key($this->rawHash, array_flip($this->WF_REQUIRED_PROP_HASH));
                $isValid = (count($this->WF_REQUIRED_PROP_HASH) == count($matchPropList));
            break;
            default:
                throw new \Exception('Validation of message type ' . $this->rawHash['type'] . ' is not implemented yet, please do');

        }
        if (!$isValid)
            throw new \Exception('Invalid Message');
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
