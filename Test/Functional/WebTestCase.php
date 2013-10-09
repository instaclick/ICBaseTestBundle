<?php
/**
 * @copyright 2012 Instaclick Inc.
 */

namespace IC\Bundle\Base\TestBundle\Test\Functional;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\DataFixtures\Purger\ORMPurger;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase as BaseWebTestCase;
use IC\Bundle\Base\TestBundle\Test\Loader;

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

    const FIXTURES_PURGE_MODE = ORMPurger::PURGE_MODE_DELETE;

    const AUTOLOAD_FIXTURES = true;

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
     * {@inheritdoc}
     */
    protected function setUp()
    {
        parent::setUp();

        // Initialize the client; it is used in all loaders and helpers
        $this->client = static::initializeClient();

        if (!static::AUTOLOAD_FIXTURES) {
            return null;
        }

        $fixtureList = static::getFixtureList();

        if (empty($fixtureList) && ! $this->forceSchemaLoad) {
            return;
        }

        $this->loadFixtures($fixtureList);
    }

    /**
     * Unset properties belonging to a class
     *
     * @param mixed $object
     */
    private function unsetPropertyList($object)
    {
        foreach ($object->getProperties() as $property) {
            if ($property->isStatic() || 0 === strncmp($property->getDeclaringClass()->getName(), 'PHPUnit_', 8)) {
                continue;
            }

            $property->setAccessible(true);
            $property->setValue($this, null);
        }
    }

    /**
     * {@inheritdoc}
     */
    protected function tearDown()
    {
        parent::tearDown();

        for ($reflection = new \ReflectionObject($this);
            $reflection !== false;
            $reflection = $reflection->getParentClass()
        ) {
            $this->unsetPropertyList($reflection);
        }
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

    /**
     * @param string|array $fixtureList
     * @param bool         $mergeWithDefault
     *
     * @throws \InvalidArgumentException
     */
    final public function loadFixtures($fixtureList, $mergeWithDefault = false)
    {
        if (!is_string($fixtureList) && !is_array($fixtureList)) {
            $type = gettype($fixtureList);

            throw new \InvalidArgumentException(
                sprintf('Argument "$fixtureList" must be either a string or an array. Type "%s" given.', $type)
            );
        }

        if (!is_array($fixtureList)) {
            $fixtureList = array($fixtureList);
        }

        if (true === $mergeWithDefault) {
            $fixtureList = array_merge(
                $fixtureList,
                static::getFixtureList()
            );
        }

        $fixtureLoader = new Loader\FixtureLoader($this->client, static::FIXTURES_PURGE_MODE);
        $executor      = $fixtureLoader->load(static::MANAGER_NAME, $fixtureList);

        $this->referenceRepository = $executor->getReferenceRepository();

        $cacheDriver = $this
            ->referenceRepository
            ->getManager()
            ->getMetadataFactory()
            ->getCacheDriver()
        ;

        if ($cacheDriver) {
            $cacheDriver->deleteAll();
        }
    }

    /**
     * Overwrite assertNull to avoid segmentation fault
     * when comparing to Objects.
     *
     * @param mixed  $actual  Actual value
     * @param string $message Message
     */
    public static function assertNull($actual, $message = '')
    {
        self::assertTrue(is_null($actual), $message);
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
