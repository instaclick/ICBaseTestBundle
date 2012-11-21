<?php
/**
 * @copyright 2012 Instaclick Inc.
 */

namespace IC\Bundle\Base\TestBundle\Test;

use Doctrine\Common\Collections\ArrayCollection;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase as BaseWebTestCase;

/**
 * Abstract class for Web test cases
 * Implementation inspired by LiipFunctionalTestBundle.
 *
 * @author Juti Noppornpitak <jnopporn@shiroyuki.com>
 * @author Guilherme Blanco <guilhermeblanco@hotmail.com>
 * @author Lukas Kahwe Smith <smith@pooteeweet.org>
 * @author Danilo Cabello <danilo.cabello@gmail.com>
 */
abstract class WebTestCase extends BaseWebTestCase
{
    const ENVIRONMENT = 'test';

    const MANAGER_NAME = null;

    /**
     * @var boolean
     */
    protected $forceSchemaLoad = false;

    /**
     * @var \Symfony\Bundle\FrameworkBundle\Client
     */
    private $client = null;

    /**
     * @var \Doctrine\Common\DataFixtures\ReferenceRepository
     */
    private $referenceRepository = null;

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
        $this->client     = static::initializeClient();
        $this->helperList = static::initializeHelperList();

        // Only initialize schema and fixtures if any are defined
        $fixtureList = static::getFixtureList();

        if (empty($fixtureList) && ! $this->forceSchemaLoad) {
            return;
        }

        $fixtureLoader = new Loader\FixtureLoader($this->client);
        $executor      = $fixtureLoader->load(static::MANAGER_NAME, $fixtureList);

        $this->referenceRepository = $executor->getReferenceRepository();

        $cacheDriver = $this->referenceRepository->getManager()->getMetadataFactory()->getCacheDriver();

        if ($cacheDriver) {
            $cacheDriver->deleteAll();
        }
    }

    /**
     * {@inheritdoc}
     */
    public function tearDown()
    {
        parent::tearDown();

        $refl = new \ReflectionObject($this);

        foreach ($refl->getProperties() as $prop) {
            if (!$prop->isStatic() && 0 !== strpos($prop->getDeclaringClass()->getName(), 'PHPUnit_')) {
                $prop->setAccessible(true);
                $prop->setValue($this, null);
            }
        }

        $this->client              = null;
        $this->helperList          = null;
        $this->referenceRepository = null;
    }

    /**
     * Retrieve the associated client instance.
     *
     * @return \Symfony\Bundle\FrameworkBundle\Client
     */
    public function getClient()
    {
        return $this->client;
    }

    /**
     * Retrieve the associated reference repository.
     *
     * @return \Doctrine\Common\DataFixtures\ReferenceRepository
     */
    public function getReferenceRepository()
    {
        return $this->referenceRepository;
    }

    /**
     * Retrieve a mock object of a given class name.
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
     * Retrieve a helper instance giving a helper name.
     *
     * @param string $name
     *
     * @return \IC\Bundle\Base\TestBundle\Test\Helper\AbstractHelper
     */
    public function getHelper($name)
    {
        $helperClass = $this->helperList->get($name);

        return new $helperClass($this);
    }

    /**
     * Overwrite assertNull to avoid segmentation fault 
     * when comparing to Objects.
     *
     * @param mixed  $actual
     * @param string $message
     */
    public function assertNull($actual, $message = null)
    {
        return $this->assertTrue(is_null($actual), $message);
    }

    /**
     * Initialize test case client
     *
     * @return \Symfony\Bundle\FrameworkBundle\Client
     */
    protected static function initializeClient()
    {
        return static::createClient(
            array('environment' => static::ENVIRONMENT),
            static::getServerParameters()
        );
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
        ));
    }

    /**
     * Overwritable method for client's server configuration
     *
     * @return array
     */
    protected static function getServerParameters()
    {
        return array();
    }

    /**
     * Overwritable method for fixtures importing
     *
     * @return array
     */
    protected static function getFixtureList()
    {
        return array();
    }
}
