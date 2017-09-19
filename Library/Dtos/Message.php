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

    public function __construct(string $rawHash) {
        if (!($rawHash = json_decode($msg->payload, true))){
            throw new \Exception('Not valid message');
        }
        $this->rawHash = $rawHash;
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
