<?php
/**
 * @copyright 2013 Instaclick Inc.
 */

namespace IC\Bundle\Base\TestBundle\Test;

use Symfony\Bundle\FrameworkBundle\Tests\TestCase as BaseTestCase;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Util\Inflector;

/**
 * Abstract class for Unit test cases
 *
 * @author Guilherme Blanco <guilhermeblanco@hotmail.com>
 * @author John Cartwright <jcartdev@gmail.com>
 */
abstract class TestCase extends BaseTestCase
{
    /**
     * @var \Doctrine\Common\Collections\ArrayCollection
     */
    private $helperList;

    /**
     * {@inheritdoc}
     */
    protected function setUp()
    {
        parent::setUp();

        $this->helperList = new ArrayCollection();
    }

    /**
     * {@inheritdoc}
     */
    protected function tearDown()
    {
        unset($this->helperList);

        parent::tearDown();
    }

    /**
     * Retrieve a helper instance giving a helper name.
     *
     * @param string $name
     *
     * @return \IC\Bundle\Base\TestBundle\Test\Helper\AbstractHelper
     */
    public function getHelper($name)
    {
        if ( ! $this->helperList) {
            $this->helperList = new ArrayCollection();
        }
        
        $normalizedName  = $this->helperList->containsKey($name)
            ? $name
            : Inflector::classify(str_replace('/', '\\', $name));

        $helperClass     = $this->helperList->containsKey($normalizedName)
            ? $this->helperList->get($normalizedName)
            : sprintf('%s\Helper\%sHelper', __NAMESPACE__, $normalizedName);

        if ( ! is_string($helperClass)) {
            return $helperClass;
        }

        $reflectionClass = new \ReflectionClass($helperClass);

        if ($reflectionClass->isAbstract() || $reflectionClass->isInterface()) {
            throw new \InvalidArgumentException(
                sprintf('Cannot create a non-implemented helper "%s".', $helperClass)
            );
        }

        $helper = new $helperClass($this);

        $this->helperList->set($normalizedName, $helper);

        return $helper;
    }

    /**
     * Create a mock object of a given class name.
     *
     * @param string $class      Class name
     * @param array  $methodList A list of methods to mock
     *
     * @return mixed
     */
    public function createMock($class, $methodList = array())
    {
        return $this
            ->getMockBuilder($class)
            ->setMethods($methodList)
            ->disableOriginalConstructor()
            ->getMock();
    }

    /**
     * Create a mock object of a given abstract class name.
     *
     * @param string $class Class name
     *
     * @return mixed
     */
    public function createAbstractMock($class)
    {
        $methodList = $this->getMethodList($class);

        return $this
            ->getMockBuilder($class)
            ->setMethods($methodList)
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();
    }

    /**
     * Make private and protected function callable
     *
     * @param mixed  $object   Subject under test
     * @param string $function Function name
     *
     * @return \ReflectionMethod
     */
    public function makeCallable($object, $function)
    {
        $method = new \ReflectionMethod($object, $function);
        $method->setAccessible(true);

        return $method;
    }

    /**
     * Sets the given property to given value on Object in Test
     *
     * @param mixed  $object Subject under test
     * @param string $name   Property name
     * @param mixed  $value  Value
     */
    public function setPropertyOnObject($object, $name, $value)
    {
        $property = new \ReflectionProperty($object, $name);
        $property->setAccessible(true);
        $property->setValue($object, $value);
    }

    /**
     * Retrieve all the methods from a given class.
     *
     * @param string $class Class Name
     *
     * @return array
     */
    private function getMethodList($class)
    {
        $reducer = function(\ReflectionMethod $method) {
            return $method->getName();
        };

        $reflector = new \ReflectionClass($class);

        return array_map($reducer, $reflector->getMethods());
    }
}
