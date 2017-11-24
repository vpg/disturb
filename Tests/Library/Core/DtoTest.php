<?php

namespace Tests\Library\Core\Dto;

use \phalcon\Config;
use Vpg\Disturb\Core\Dto;


/**
 * Dto test class
 *
 * @author  JEROME BOURGEAIS <jbourgeais@voyageprive.com>
 */
class DtoTest extends \Tests\DisturbUnitTestCase
{

    /**
     * Test dto instanciation
     *
     * @return void
     */
    public function testWrongDtoInput()
    {
        // Check wrong input type
        $dataMixed = 'foo';
        $this->expectException(Dto\InvalidInputTypeException::class);
        $dtoMock = $this->getMockBuilder('\Vpg\Disturb\Core\Dto\AbstractDto')
            ->setConstructorArgs([$dataMixed])
            ->getMockForAbstractClass();
    }

    /**
     * Test getMissingPropertyList from Hash method
     *
     * @return void
     */
    public function testGetMissingDeepPropertyFromHash()
    {
        // Testing hash
        $dataHash = [
            'foo' => 'a foo',
            'bar' => 2,
            'gar' => ['goo' => ['boo' => 'a boo']]
        ];
        $dtoMock = $this->getMockBuilder('\Vpg\Disturb\Core\Dto\AbstractDto')
            ->setConstructorArgs([$dataHash])
            ->getMockForAbstractClass();

        $requiredPropList = [
            'foo',
            ['gar', 'goo', 'boo']
        ];
        $missingPropList = $dtoMock->getMissingPropertyList($requiredPropList);
        $this->assertEmpty($missingPropList);

        $requiredPropList = [
            'foo',
            'far',
            ['gar', 'goo', 'boo', 'foo']
        ];
        $missingPropList = $dtoMock->getMissingPropertyList($requiredPropList);
        $this->assertEquals(
            [
                'far',
                ['gar', 'goo', 'boo', 'foo']
            ],
            $missingPropList
        );
    }

    /**
     * Test getMissingPropertyList from Hash method
     *
     * @return void
     */
    public function testGetMissingPropertyFromHash()
    {
        // Testing hash
        $dataHash = ['foo' => 'a foo', 'bar' => 2];
        $dtoMock = $this->getMockBuilder('\Vpg\Disturb\Core\Dto\AbstractDto')
            ->setConstructorArgs([$dataHash])
            ->getMockForAbstractClass();

        $requiredPropList = ['foo', 'bar', 'goo'];
        $missingPropList = $dtoMock->getMissingPropertyList($requiredPropList);
        $this->assertEquals(
            [
                'goo'
            ],
            $missingPropList
        );
    }

    /**
     * Test getMissingPropertyList from phalcon config method
     *
     * @return void
     */
    public function testGetMissingPropertyFromConfig()
    {
        // testing Phalcon config
        $dataConfig = new \Phalcon\Config(
            [
                'foo' => 'a foo',
                'bar' => 2
            ]
        );
        $dtoMock = $this->getMockBuilder('\Vpg\Disturb\Core\Dto\AbstractDto')
            ->setConstructorArgs([$dataConfig])
            ->getMockForAbstractClass();

        $requiredPropList = ['foo', 'bar', 'goo'];
        $missingPropList = $dtoMock->getMissingPropertyList($requiredPropList);
        $this->assertEquals(
            [
                'goo'
            ],
            $missingPropList
        );
    }
}
