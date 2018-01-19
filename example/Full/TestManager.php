<?php
namespace Vpg\Disturb\Example\Test;

use \Vpg\Disturb;
use \Vpg\Disturb\Client;

class TestManager implements Disturb\Workflow\ManagerServiceInterface
{

    public function getStepInput(string $contractCode, string $stepCode) : array {
        switch ($stepCode) {
        case 'start':
            $stepPayloadHash = [
                [
                    'contract' => $contractCode,
                    'step'     => $stepCode,
                    'todo'     => 'start'
                ]
            ];
            break;
        case 'foo':
            $stepPayloadHash = [
                [
                    'contract' => $contractCode,
                    'step'     => $stepCode,
                    'todo'     => 'foo'
                ]
            ];
            break;
        case 'bar':
            $stepPayloadHash = [
                [
                    'contract' => $contractCode,
                    'step'     => $stepCode,
                    'todo'     => 'bar1'
                ],
                [
                    'contract' => $contractCode,
                    'step'     => $stepCode,
                    'todo'     => 'bar2'
                ]
            ];
            break;
        case 'far':
            $stepPayloadHash = [
                [
                    'contract' => $contractCode,
                    'step'     => $stepCode,
                    'todo'     => 'far1'
                ],
                [
                    'contract' => $contractCode,
                    'step'     => $stepCode,
                    'todo'     => 'far2'
                ],
                [
                    'contract' => $contractCode,
                    'step'     => $stepCode,
                    'todo'     => 'far3'
                ]
            ];
            break;
        case 'boo':
            $stepPayloadHash = [
                [
                    'contract' => $contractCode,
                    'step'     => $stepCode,
                    'todo'     => 'boo'
                ]
            ];
            break;
        case 'end':
            $stepPayloadHash = [
                [
                    'contract' => $contractCode,
                    'step'     => $stepCode,
                    'todo'     => 'end'
                ]
            ];
            break;
        default :
            // trhow exc
        }
        return $stepPayloadHash;
    }

}
