<?php

namespace Tests\Library\Context;

use \phalcon\Config;
use Vpg\Disturb\Context;


/**
 * Context Dto test class
 *
 * @author  JEROME BOURGEAIS <jbourgeais@voyageprive.com>
 */
class ContextDtoTest extends \Tests\DisturbUnitTestCase
{

        private $contextHash = [
            'steps' => [
                [
                    'name' => 'bar',
                    'jobList' => [
                        [
                            'result' => [
                                'bar' => 'a bar'
                            ],
                            'id' => 0,
                        ],
                        [
                            'result' => [
                                'bar' => 'a second bar'
                            ],
                            'id' => 1,
                        ]
                    ],
                ],
                [
                    'name' => 'foo',
                    'jobList' => [
                        [
                            'result' => [
                                'foo' => 'a foo'
                            ],
                            'id' => 0,
                        ]
                    ]
                ],
                [
                    'name' => 'far',
                    'jobList' => [
                        [
                            'result' => [],
                            'id' => 0,
                        ]
                    ]
                ],
                [
                    'name' => 'boo',
                    'jobList' => [
                        [
                            'result' => [],
                            'id' => 0,
                        ],
                        [
                            'result' => [],
                            'id' => 1,
                        ]
                    ]
                ]
            ],
            'initialPayload' => [
                'contract_id' => '15646',
                'bu' => 'fr',
            ],
            'status' => 'STARTED',
            'currentStepPos' => 0,
            'initializedAt' => '2017-12-01 21:06:15',
            'updatedAt' => '2017-12-01 21:06:15'
        ];

    /**
     * Test getStepResultData
     *
     * @return void
     */
    public function testGetInitialPayload()
    {
        // Testing hash
        $contextDto = new Context\ContextDto($this->contextHash);
        $initialPayloadHash = $contextDto->getInitialPayload();
        $this->assertEquals(
            [
                'contract_id' => '15646',
                'bu' => 'fr',
            ],
            $initialPayloadHash
        );
    }

    /**
     * Test getStepResultData
     *
     * @return void
     */
    public function testGetStepResultData()
    {
        // Testing hash
        $contextDto = new Context\ContextDto($this->contextHash);
        $stepResultHash = $contextDto->getStepResultData();
        $this->assertEquals(
            [
                'bar' => [
                    ['bar' => 'a bar'],
                    ['bar' => 'a second bar']
                ],
                'foo' => [
                    ['foo' => 'a foo']
                ]
            ],
            $stepResultHash
        );
    }
}
