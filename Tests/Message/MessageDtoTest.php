<?php

namespace Tests\Library\Message;

use \phalcon\Config;
use \Vpg\Disturb\Message;


/**
 * Context Dto test class
 *
 * @author  JEROME BOURGEAIS <jbourgeais@voyageprive.com>
 */
class MessageDtoTest extends \Tests\DisturbUnitTestCase
{

        private $validWfStartMessageHash = [
            'id'      => 'test1',
            'type'    => Message\MessageDto::TYPE_WF_CTRL,
            'action'  => Message\MessageDto::ACTION_WF_CTRL_START,
            'payload' => ['foo' => 'bar']
        ];
        private $validStepCtrlMessageHash = [
            'id'      => 'test1',
            'type'    => Message\MessageDto::TYPE_STEP_CTRL,
            'action'  => Message\MessageDto::ACTION_WF_CTRL_START,
            'payload' => ['foo' => 'bar']
        ];
        private $validStepAckMessageHash = [
            'id'      => 'test1',
            'type'    => Message\MessageDto::TYPE_STEP_ACK,
            'jobId'  => '1',
            'result' => ['foo' => 'bar', 'status' => Message\MessageDto::MSG_RETURN_SUCCESS]
        ];

        private $invalidMessageHash = [
        ];

    /**
     * Test dto instantiation
     *
     * @return void
     */
    public function testInstantiateDto()
    {
        $this->expectException(Message\InvalidMessageException::class);
        $messageDto = new Message\MessageDto($this->invalidMessageHash);
        $messageDto = new Message\MessageDto($this->validWfStartMessageHash);
        $messageId = $messageDto->getId();
        $this->assertEquals(
            $this->validMessageHash['id'],
            $initialPayloadHash
        );
    }

    /**
     * Test dto getter
     *
     * @return void
     */
    public function testWfCtrlMessage()
    {
        $messageDto = new Message\MessageDto($this->validWfStartMessageHash);
        $this->assertEquals($this->validWfStartMessageHash['id'], $messageDto->getId());
        $this->assertEquals($this->validWfStartMessageHash['type'], $messageDto->getType());
        $this->assertEquals($this->validWfStartMessageHash['action'], $messageDto->getAction());
        $this->assertEquals($this->validWfStartMessageHash['payload'], $messageDto->getPayload());
        $this->assertEquals(json_encode($this->validWfStartMessageHash), (string)$messageDto);
    }

    /**
     * Test dto getter
     *
     * @return void
     */
    public function testStepCtrlMessage()
    {
        $messageDto = new Message\MessageDto($this->validStepCtrlMessageHash);
        $this->assertEquals($this->validStepCtrlMessageHash['id'], $messageDto->getId());
        $this->assertEquals($this->validStepCtrlMessageHash['type'], $messageDto->getType());
        $this->assertEquals($this->validStepCtrlMessageHash['action'], $messageDto->getAction());
        $this->assertEquals($this->validStepCtrlMessageHash['payload'], $messageDto->getPayload());
    }

    /**
     * Test dto getter
     *
     * @return void
     */
    public function testStepAckMessage()
    {
        $messageDto = new Message\MessageDto($this->validStepAckMessageHash);
        $this->assertEquals($this->validStepAckMessageHash['id'], $messageDto->getId());
        $this->assertEquals($this->validStepAckMessageHash['type'], $messageDto->getType());
        $this->assertEquals($this->validStepAckMessageHash['jobId'], $messageDto->getJobId());
        $this->assertEquals($this->validStepAckMessageHash['result'], $messageDto->getResult());
        $this->assertEquals($this->validStepAckMessageHash['result']['status'], $messageDto->getStepResultStatus());
    }
}
