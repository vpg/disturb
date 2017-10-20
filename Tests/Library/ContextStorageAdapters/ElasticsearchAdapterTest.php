<?php

namespace Tests\Library\Context;

use \Disturb\Context\Elasticsearch;
use Phalcon\Exception;

/**
 * Elasticsearch context storage Test class
 *
 * @author  Alexandre DEFRETIN <adefretin@voyageprive.com>
 */
class ElasticsearchTest extends \Tests\DisturbUnitTestCase
{
    /**
     * @var \Disturb\Context\Elasticsearch $elastic
     */
    private $elastic;

    /**
     * Setup
     */
    public function setUp()
    {
        parent::setUp();

        $this->elastic = new \Disturb\Context\Elasticsearch();
    }

    /**
     * Test initConfig method
     */
    public function testInitConfig()
    {
        // file not found
        try {
            $this->invokeMethod($this->elastic, 'initConfig', ['bad/path']);
        } catch (\Exception $exception) {
            if ($exception) {
                $this->assertEquals('Elasticsearch config not found', $exception->getMessage());
            } else {
                $this->fail('Exception expected : Elasticsearch config not found');
            }
        }

        // file found but not json
        try {
            $this->invokeMethod(
                $this->elastic,
                'initConfig',
                [
                    realpath(__DIR__ . '/../../phpunit.xml')
                ]
            );
        } catch (\Exception $exception) {
            if ($exception) {
                $this->assertEquals('The argument is not initialized or iterable()', $exception->getMessage());
            } else {
                $this->fail('Exception expected : The argument is not initialized or iterable()');
            }
        }

        // success
        $this->invokeMethod($this->elastic, 'initConfig');
        $config = $this->getProperty($this->elastic, 'config');
        $this->assertNotEmpty($config->host);
        $this->assertNotEmpty($config->port);
        $this->assertNotEmpty($config->scheme);
        $this->assertEquals('disturb_context', $config->context->index);
        $this->assertNotEmpty($config->context->type);
    }
}