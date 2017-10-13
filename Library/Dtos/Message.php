<?
namespace Disturb;

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

    const MSG_RETURN_SUCCESS = 'SUCCESS';
    const MSG_RETURN_ERROR = 'ERROR';

    private $rawHash = [];

    public function __construct(string $rawPayload) {
        if (!($rawHash = json_decode($rawPayload, true))){
            // xxx defined typed Exception
            throw new \Exception('Not able to parse message');
        }
        $this->rawHash = $rawHash;
        $this->validate();
    }

    public function validate()
    {
        if (!isset($this->rawHash['type'])) {
            // xxx defined typed Exception
            throw new \Exception('Missing message Type');
        }
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

    public function getPayload() : array {
        return !empty($this->rawHash['payload']) ? $this->rawHash['payload'] : [];
    }
}
