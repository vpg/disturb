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
            'type'    => Message\MessageDto::TYPE_STEP_CTRL,
            'action'  => Message\MessageDto::ACTION_WF_CTRL_START,
            'payload' => ['foo' => 'bar']
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
    public function testGetters()
    {
        $messageDto = new Message\MessageDto($this->validWfStartMessageHash);
        $this->assertEquals($this->validWfStartMessageHash['id'], $messageDto->getId());
        $this->assertEquals($this->validWfStartMessageHash['type'], $messageDto->getType());
        $this->assertEquals($this->validWfStartMessageHash['action'], $messageDto->getAction());
        $this->assertEquals($this->validWfStartMessageHash['payload'], $messageDto->getPayload());
    }
}
