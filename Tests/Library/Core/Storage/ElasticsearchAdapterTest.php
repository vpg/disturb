<?php

namespace Tests\Library\Core\Storage;

use Vpg\Disturb\Core\Storage\StorageAdapterFactory;
use Vpg\Disturb\Core\Storage\ElasticsearchAdapter;
use Vpg\Disturb\Core\Storage\StorageException;

use Phalcon\Config\Adapter\Json;

/**
 * Elasticsearch context storage Test class
 *
 * @author  Alexandre DEFRETIN <adefretin@voyageprive.com>
 */
class ElasticsearchAdapterTest extends \Tests\DisturbUnitTestCase
{
    /**
     * @const string TEST_DOCUMENT_EMPTY_ID
     */
    const TEST_DOCUMENT_EMPTY_ID = '';

    /**
     * @const string TEST_DOCUMENT_ID
     */
    const TEST_DOCUMENT_ID = 'doc_test';

    /**
     * @const string TEST_DOCUMENT_FAKE_ID
     */
    const TEST_DOCUMENT_FAKE_ID = 'fake_doc_test';

    /**
     * @const array TEST_DOCUMENT
     */
    const TEST_DOCUMENT = [
        'key1' => 'content1',
        'key2' => 'content2'
    ];

    /**
     * @var string $elasticsearchTestHost
     */
    private static $elasticsearchTestHost;

    /**
     * @var ElasticsearchAdapter $elasticsearchAdapter
     */
    private static $elasticsearchAdapter;

    /**
     * Setup
     *
     * @return void
     */
    public static function setUpBeforeClass()
    {
        parent::setUpBeforeClass();

        self::$elasticsearchAdapter = new ElasticsearchAdapter();

        $contextStorageConfig = new Json(realpath(__DIR__ . '/Config/elasticsearchConfig.json'));
        self::$elasticsearchTestHost = $contextStorageConfig[ElasticsearchAdapter::CONFIG_HOST];
        $config = new Json(
            realpath(__DIR__ . '/Config/elasticsearchConfig.json')
        );
        self::$elasticsearchAdapter->initialize($config, StorageAdapterFactory::USAGE_MONITORING);
    }

    /**
     * Test initConfig method
     *
     * @return void
     */
    public function testInitConfig()
    {
        // config not found
        $elasticsearchAdapter = new ElasticsearchAdapter();
        try {
            $this->invokeMethod($elasticsearchAdapter, 'initConfig', '');
        } catch (\Throwable $exception) {
            if (!$exception) {
                $this->fail('Exception expected : Elasticsearch config not found');
            }
        }

        // missing required config field
        $uncompletedConfig = new Json(
            realpath(__DIR__ . '/Config/elasticsearchUncompletedConfig.json')
        );
        try {
            $this->invokeMethod(
                $elasticsearchAdapter,
                'initConfig',
                [$uncompletedConfig, ElasticsearchAdapter::USAGE_MONITORING_CONFIG]
            );
        } catch (StorageException $exception) {
            if ($exception) {
                $this->assertEquals(
                    'Vpg\Disturb\Core\Storage\ElasticsearchAdapter::initConfig : config ' .
                    ElasticsearchAdapter::CONFIG_HOST . ' not found',
                    $exception->getMessage()
                );
            } else {
                $this->fail('Exception expected : Vpg\Disturb\Core\Storage\ElasticsearchAdapter::initConfig : 
                config ' . ElasticsearchAdapter::CONFIG_HOST . ' not found');
            }
        }


        $adapterConfig = $this->getProperty(self::$elasticsearchAdapter, 'config');
        $this->assertEquals(self::$elasticsearchTestHost, $adapterConfig[ElasticsearchAdapter::CONFIG_HOST]);

        $this->assertEquals(
            ElasticsearchAdapter::USAGE_MONITORING_CONFIG[ElasticsearchAdapter::DOC_INDEX],
            $adapterConfig[ElasticsearchAdapter::DOC_INDEX]
        );

        $this->assertEquals(
            'worker',
            $adapterConfig[ElasticsearchAdapter::DOC_TYPE]
        );
    }

    /**
     * Test checkVendorLibraryAvailable method
     *
     * @return void
     */
    public function testCheckVendorLibraryAvailable()
    {
        $badVendorLibraryName = 'badVendorLibraryName';
        try {
            $this->invokeMethod(self::$elasticsearchAdapter, 'checkVendorLibraryAvailable', [$badVendorLibraryName]);
        } catch (StorageException $exception) {
            if ($exception) {
                $this->assertEquals(
                    'Vpg\Disturb\Core\Storage\ElasticsearchAdapter::checkVendorLibraryAvailable : ' .
                    $badVendorLibraryName . ' lib not found. Please make "composer update"',
                    $exception->getMessage()
                );
            } else {
                $this->fail(
                    'Vpg\Disturb\Core\Storage\ElasticsearchAdapter::checkVendorLibraryAvailable : ' .
                    $badVendorLibraryName . ' lib not found.  Please make "composer update"'
                );
            }
        }

        try {
            $this->invokeMethod(
                self::$elasticsearchAdapter,
                'checkVendorLibraryAvailable',
                [ElasticsearchAdapter::VENDOR_CLASSNAME]
            );

        } catch (\Exception $exception) {
            $this->fail('Exception expected : ' . ElasticsearchAdapter::VENDOR_CLASSNAME . ' lib not found.
             Please make "composer update"');
        }
    }

    /**
     * Test checkVendorLibraryAvailable method
     *
     * @return void
     */
    public function testInitCommonRequestParams()
    {
        $this->invokeMethod(self::$elasticsearchAdapter, 'initCommonRequestParams', []);
        $commonRequestParamHash = $this->getProperty(self::$elasticsearchAdapter, 'commonRequestParamHash');

        $this->assertEquals(
            ElasticsearchAdapter::USAGE_MONITORING_CONFIG[ElasticsearchAdapter::DOC_INDEX],
            $commonRequestParamHash[ElasticsearchAdapter::DOC_INDEX]
        );

        $this->assertEquals(
            'worker',
            $commonRequestParamHash[ElasticsearchAdapter::DOC_TYPE]
        );
    }

    /**
     * Test init client
     *
     * @return void
     */
    public function testInitClient()
    {
        // host unavailable
        $config = new Json(realpath(__DIR__ . '/Config/elasticsearchBadConfig.json'));
        $elasticsearchAdapter = new ElasticsearchAdapter();
        try {
        $elasticsearchAdapter->initialize($config, StorageAdapterFactory::USAGE_MONITORING);

        } catch (StorageException $exception) {
            if ($exception) {
                $this->assertEquals(
                    'Vpg\Disturb\Core\Storage\ElasticsearchAdapter::initClient : host : ' .
                    $config[ElasticsearchAdapter::CONFIG_HOST] . ' not available',
                    $exception->getMessage()
                );
            } else {
                $this->fail(
                    'Vpg\Disturb\Core\Storage\ElasticsearchAdapter::initClient : host : ' .
                    $config[ElasticsearchAdapter::CONFIG_HOST] . ' not available'
                );
            }
        }

        try {
            $this->invokeMethod(
                self::$elasticsearchAdapter,
                'initClient',
                []
            );
        } catch (\Exception $exception) {
            $this->fail('ElasticsearchAdapter initClient : ' . $exception->getMessage());
        }

        // Bad init
        $config = new Json(realpath(__DIR__ . '/Config/elasticsearchConfig.json'));
        $elasticsearchAdapter = new ElasticsearchAdapter();
        $this->expectException(StorageException::class);
        $elasticsearchAdapter->initialize($config, 'badUsage');
    }

    /**
     * Test save
     *
     * @return void
     */
    public function testSave()
    {
        // bad parameter
        try {
            $this->invokeMethod(
                self::$elasticsearchAdapter,
                'save',
                [self::TEST_DOCUMENT_EMPTY_ID, []]
            );
        } catch (\Exception $exception) {
            if ($exception) {
                $this->assertEquals($exception->getCode(), StorageException::CODE_INVALID_PARAMETER);
            } else {
                $this->fail('Exception code expected : ' . StorageException::CODE_INVALID_PARAMETER);
            }
        }

        // succedeed
        try {
            $this->invokeMethod(
                self::$elasticsearchAdapter,
                'save',
                [self::TEST_DOCUMENT_ID, self::TEST_DOCUMENT]
            );
        } catch (\Exception $exception) {
            $this->fail('Elasticsearch save failed : ' . $exception->getMessage());
        }


        $this->expectException(StorageException::class);
        $f = self::$elasticsearchAdapter->update('', self::TEST_DOCUMENT);
    }

    /**
     * Test bad update
     *
     * @return void
     */
    public function testBadUpdate()
    {
        $this->expectException(StorageException::class);
        $f = self::$elasticsearchAdapter->update(self::TEST_DOCUMENT_ID, [
            'script' => [
                'source' => 'ctx._source.counter += params.count',
                'lang' => 'groovy',
                'params' => [
                    'count' => 4
                ]
            ]
        ]);
    }


    /**
     * Test get
     *
     * @return void
     */
    public function testGet()
    {
        // bad parameter
        try {
            $this->invokeMethod(
                self::$elasticsearchAdapter,
                'get',
                [self::TEST_DOCUMENT_EMPTY_ID]
            );
        } catch (\Exception $exception) {
            if ($exception) {
                $this->assertEquals($exception->getCode(), StorageException::CODE_INVALID_PARAMETER);
            } else {
                $this->fail('Exception code expected : ' . StorageException::CODE_INVALID_PARAMETER);
            }
        }

        // document not found
        try {
            $this->invokeMethod(
                self::$elasticsearchAdapter,
                'get',
                [self::TEST_DOCUMENT_FAKE_ID]
            );
        } catch (\Exception $exception) {
            if ($exception) {
                $this->assertEquals($exception->getCode(), StorageException::CODE_GET);
            } else {
                $this->fail('Exception code expected : ' . StorageException::CODE_GET);
            }
        }

        // document found
        try {
            $docHash = $this->invokeMethod(
                self::$elasticsearchAdapter,
                'get',
                [self::TEST_DOCUMENT_ID]
            );

            $this->assertArraysEquals(self::TEST_DOCUMENT, $docHash);
        } catch (\Exception $exception) {
            $this->fail($exception->getMessage());
        }
    }

    /**
     * Test exist function
     *
     * @return void
     */
    public function testExist()
    {
        // bad parameter
        try {
            $this->invokeMethod(
                self::$elasticsearchAdapter,
                'exists',
                [self::TEST_DOCUMENT_EMPTY_ID]
            );
        } catch (\Exception $exception) {
            if ($exception) {
                $this->assertEquals($exception->getCode(), StorageException::CODE_INVALID_PARAMETER);
            } else {
                $this->fail('Exception code expected : ' . StorageException::CODE_INVALID_PARAMETER);
            }
        }

        // document not exists
        try {
            $doesDocumentExist = $this->invokeMethod(
                self::$elasticsearchAdapter,
                'exists',
                [self::TEST_DOCUMENT_FAKE_ID]
            );
            $this->assertFalse($doesDocumentExist);
        } catch (\Exception $exception) {
            if ($exception) {
                $this->assertEquals($exception->getCode(), StorageException::CODE_EXIST);
            } else {
                $this->fail('Exception code expected : ' . StorageException::CODE_EXIST);
            }
        }

        // document exists
        try {
            $doesDocumentExist = $this->invokeMethod(
                self::$elasticsearchAdapter,
                'exists',
                [self::TEST_DOCUMENT_ID]
            );
            $this->assertTrue($doesDocumentExist);
        } catch (\Exception $exception) {
            if ($exception) {
                $this->assertEquals($exception->getCode(), StorageException::CODE_EXIST);
            } else {
                $this->fail('Exception code expected : ' . StorageException::CODE_EXIST);
            }
        }

        $this->expectException(StorageException::class);
        $f = self::$elasticsearchAdapter->exists('');
    }

    /**
     * Test delete function
     *
     * @return void
     */
    public function testDelete()
    {
        // bad parameter
        try {
            $this->invokeMethod(
                self::$elasticsearchAdapter,
                'delete',
                [self::TEST_DOCUMENT_EMPTY_ID]
            );
        } catch (\Exception $exception) {
            if ($exception) {
                $this->assertEquals($exception->getCode(), StorageException::CODE_INVALID_PARAMETER);
            } else {
                $this->fail('Exception code expected : ' . StorageException::CODE_INVALID_PARAMETER);
            }
        }

        // succedeed
        try {
            // check if test document exist
            $doesDocumentExist = $this->invokeMethod(
                self::$elasticsearchAdapter,
                'exists',
                [self::TEST_DOCUMENT_ID]
            );
            $this->assertTrue($doesDocumentExist);

            // deleting test document
            $this->invokeMethod(
                self::$elasticsearchAdapter,
                'delete',
                [self::TEST_DOCUMENT_ID]
            );

            // check if test document is correctly deleted
            $doesDocumentExist = $this->invokeMethod(
                self::$elasticsearchAdapter,
                'exists',
                [self::TEST_DOCUMENT_ID]
            );
            $this->assertFalse($doesDocumentExist);
        } catch (\Exception $exception) {
            if ($exception) {
                $this->assertEquals($exception->getCode(), StorageException::CODE_DELETE);
            } else {
                $this->fail('Exception code expected : ' . StorageException::CODE_DELETE);
            }
        }

        $this->expectException(StorageException::class);
        $f = self::$elasticsearchAdapter->delete('');
    }

    /**
     * Init valid config
     *
     * @return void
     */
    private function initValidConfig()
    {
        $config = new Json(
            realpath(__DIR__ . '/Config/elasticsearchConfig.json')
        );
        $this->invokeMethod(
            self::$elasticsearchAdapter,
            'initConfig',
            [$config, ElasticsearchAdapter::USAGE_MONITORING_CONFIG]
        );
    }
}
