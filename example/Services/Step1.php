<?
namespace ex\Services;

class Step1 extends \Phalcon\Mvc\User\Component
{

    // MUST be in an interface
    public function execute() : array {
        echo PHP_EOL . '>' . __METHOD__ . ' : ' . json_encode(func_get_args());
        $resultHash = [];
        for($i=0; $i<4; $i++) {
            echo '.';
            sleep(1);
        }
        $resultHash = [
            'status' => 'SUCESS', // xxx MUST be abstracted in a base classe as const
        ];
        return $resultHash;
    }
}
