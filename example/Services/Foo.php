<?
namespace Vpg\Disturb\Example\Services;

use \Vpg\Disturb\Services\ManagerServiceInterface;

class Foo implements ManagerServiceInterface
{

    public function getStepInput(string $contractCode, string $stepCode) : array {
        echo PHP_EOL . '>' . __METHOD__ . ' : ' . json_encode(func_get_args());
        switch ($stepCode) {
        case 'step0':
            $stepPayloadHash = [
                [
                    'contract' => $contractCode,
                    'step'     => $stepCode,
                    'step0_Code' => 'step0_foo'
                ]
            ];
            break;
        case 'step1':
            $stepPayloadHash = [
                [
                    'contract' => $contractCode,
                    'step'     => $stepCode,
                    'step1_Code' => 'step1_job1'
                ],
                [
                    'contract' => $contractCode,
                    'step'     => $stepCode,
                    'step1_Code' => 'step1_job2'
                ]
            ];
            break;
        case 'step2':
            $stepPayloadHash = [
                [
                    'contract' => $contractCode,
                    'step'     => $stepCode,
                    'step2_Code' => 'step2_foo'
                ]
            ];
            break;
        default :
            $stepPayloadHash['status'] = 'ERR';
        }
        echo PHP_EOL . '<' . __METHOD__ . ' : ' . json_encode($stepPayloadHash);
        return $stepPayloadHash;
    }

}
