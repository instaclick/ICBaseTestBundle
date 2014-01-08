<?php
/**
 * @copyright 2012 Instaclick Inc.
 */

namespace IC\Bundle\Base\TestBundle\Test;

use IC\Bundle\Base\TestBundle\Test\Functional\WebTestCase as BaseWebTestCase;
use Doctrine\Common\Collections\ArrayCollection;

/**
 * Abstract class for Web test cases
 *
 * @deprecated to be removed at a later date. Please use IC\Bundle\Base\TestBundle\Test\Functional\WebTestCase instead.
 */
abstract class WebTestCase extends BaseWebTestCase
{
    /**
     * @var \Doctrine\Common\Collections\ArrayCollection
     */
    private $helperList = null;

    /**
     * {@inheritdoc}
     */
    protected function setUp()
    {
        parent::setUp();

        // Initialize the client; it is used in all loaders and helpers
        $this->helperList = static::initializeHelperList();
    }

    /**
     * Create a mock object of a given class name.
     *
     * @param string $class Class name
     *
     * @return mixed
     */
    public function getClassMock($class)
    {
        return $this
            ->getMockBuilder($class)
            ->disableOriginalConstructor()
            ->getMock();
    }

    /**
     * Initialize test case helper list
     *
     * @return \Doctrine\Common\Collections\ArrayCollection
     */
    protected static function initializeHelperList()
    {
        return new ArrayCollection(array(
            'command'     => __NAMESPACE__ . '\Helper\CommandHelper',
            'controller'  => __NAMESPACE__ . '\Helper\ControllerHelper',
            'service'     => __NAMESPACE__ . '\Helper\ServiceHelper',
            'session'     => __NAMESPACE__ . '\Helper\SessionHelper',
            'validator'   => __NAMESPACE__ . '\Helper\ValidatorHelper',
            'persistence' => __NAMESPACE__ . '\Helper\PersistenceHelper',
            'route'       => __NAMESPACE__ . '\Helper\RouteHelper'
        ));
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
        $helperClass = $this->helperList->get($name);

        if ($helperClass) {
            return new $helperClass($this);
        }

        $container     = $this->getClient()->getContainer();
        $helperService = $container->get($name);

        if ($helperService) {
            $helperService->setTestCase($this);

            return $helperService;
        }
    }

    /**
     * Add helper to helper list.
     *
     * @param string $name
     * @param string $namespace
     */
    public function addHelper($name, $namespace)
    {
        $this->helperList->set($name, $namespace);
    }
}
