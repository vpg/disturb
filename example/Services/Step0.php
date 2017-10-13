<?
namespace Ex\Services;

class Step0  extends AbstractStep implements StepInterface
{
    public function execute() : array {
        echo PHP_EOL . '>' . __METHOD__ . ' : ' . json_encode(func_get_args());
        $resultHash = [];
        for($i=0; $i<4; $i++) {
            echo '.';
            sleep(1);
        }
        $resultHash = [
            'status' => $this->MSG_ACK_SUCCESS, // xxx MUST be abstracted in a base classe as const
        ];
        return $resultHash;
    }
}
