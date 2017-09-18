<?
namespace Disturb\Dtos;

class Message implements \ArrayAccess {

    public const TYPE_STEP_CTRL = 'STEP-CTRL';
    public const TYPE_STEP_ACK = 'STEP-ACK';
    public const TYPE_WF_ACT = 'WF-ACTION';
    public const TYPE_WF_CTRL = 'WF-CONTROL';
    public const TYPE_WF_MONITOR = 'WF-MONITOR';

    public const ACTION_WF_CTRL_START = 'WF-CONTROL-START';
    public const ACTION_WF_CTRL_PAUSE = 'WF-CONTROL-PAUSE';
    public const ACTION_WF_CTRL_RESUME = 'WF-CONTROL-RESUME';

    public const ACTION_WF_MONITOR_PING = 'WF-MONITOR-PING';
    public const ACTION_WF_MONITOR_PONG = 'WF-MONITOR-PONG';

    private $rawHash = [];

    public function __construct(array $rawHash) {
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
