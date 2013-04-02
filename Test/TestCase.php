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
        $normalizedName  = Inflector::classify(str_replace('/', '\\', $name));
        $helperClass     = $this->helperList->containsKey($name)
            ? $this->helperList->get($name)
            : sprintf('%s\Helper\%sHelper', __NAMESPACE__, $normalizedName);
        $reflectionClass = new \ReflectionClass($helperClass);

        if ($reflectionClass->isAbstract() || $reflectionClass->isInterface()) {
            throw new \InvalidArgumentException(
                sprintf('Cannot create a non-implemented helper "%s".', $helperClass)
            );
        }

        return new $helperClass($this);
    }

    /**
     * Create a mock object of a given class name.
     *
     * @param string $class Class name
     *
     * @return mixed
     */
    public function createMock($class)
    {
        return $this
            ->getMockBuilder($class)
            ->disableOriginalConstructor()
            ->getMock();
    }
}
