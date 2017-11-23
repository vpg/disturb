<?php

namespace Tests;

use \PHPUnit\Framework\TestCase as TestCase;

use \Phalcon\DI;
use \Phalcon\DiInterface;
use \phalcon\Di\InjectionAwareInterface;

/**
 * Common Unit Test Case Class
 *
 * @package  Tests
 * @author   Alexandre DEFRETIN <adefretin@voyageprive.com>
 * @namespace Tests
 */
abstract class DisturbUnitTestCase extends TestCase implements InjectionAwareInterface
{
    /**
     * Setup a new test case
     *
     * @return void
     */
    public function setUp()
    {
        // Reset the DI container
        $di = DI::getDefault();

        // Instantiate a new DI container
        $this->setDI($di);
    }

    protected function tearDown()
    {
        parent::tearDown();
    }

    /**
     * Sets the Dependency Injector.
     *
     * @see    Injectable::setDI
     * @param  DiInterface $di
     * @return $this
     */
    public function setDI(DiInterface $di)
    {
        $this->di = $di;

        return $this;
    }

    /**
     * Returns the internal Dependency Injector.
     *
     * @see    Injectable::getDI
     * @return DiInterface
     */
    public function getDI()
    {
        if (!$this->di instanceof DiInterface) {
            return Di::getDefault();
        }

        return $this->di;
    }

    /**
     * Call protected/private method of a class.
     *
     * @param object $object     Instantiated object that we will run method on.
     * @param string $methodName Method name to call
     * @param array  $parameters Array of parameters to pass into method.
     *
     * @return mixed Method return.
     */
    protected function invokeMethod(&$object, $methodName, array $parameters = array())
    {
        $reflection = new \ReflectionClass(get_class($object));
        $method = $reflection->getMethod($methodName);
        $method->setAccessible(true);

        return $method->invokeArgs($object, $parameters);
    }

    /**
     * Set protected/private property of a class.
     *
     * @param object $object       Instantiated object
     * @param string $propertyName Property name to change
     * @param mixed  $value        Value to set to property
     *
     * @return mixed property value
     */
    protected function setProperty(&$object, $propertyName, $value)
    {
        $reflection = new \ReflectionClass(get_class($object));
        $property = $reflection->getProperty($propertyName);
        $property->setAccessible(true);
        $property->setValue($object, $value);

        return $property->getValue($object);
    }

    /**
     * Get protected/private property of a class.
     *
     * @param object $object       Instantiated object
     * @param string $propertyName Property name to change
     *
     * @return mixed property value
     */
    protected function getProperty(&$object, $propertyName)
    {
        $reflection = new \ReflectionClass(get_class($object));
        $property = $reflection->getProperty($propertyName);
        $property->setAccessible(true);
        return $property->getValue($object);
    }

    /**
     * Assert that two array are equals
     *
     * @param array  $expectedArray expected array
     * @param array  $actualArray   actual array
     * @param string $message       message
     *
     * @return void
     */
    protected function assertArraysEquals($expectedArray, $actualArray, $message = '')
    {
        $this->arraySortRecursive($expectedArray);
        $this->arraySortRecursive($actualArray);

        $this->assertEquals(
            json_encode($expectedArray),
            json_encode($actualArray),
            $message
        );
    }

    /**
     * Sort array
     *
     * @param array $array array to sort
     *
     * @return bool
     */
    private function arraySortRecursive(&$array)
    {
        foreach ($array as &$value) {
            if (is_array($value)) {
                $this->arraySortRecursive($value);
            }
        }
        return ksort($array);
    }
}
