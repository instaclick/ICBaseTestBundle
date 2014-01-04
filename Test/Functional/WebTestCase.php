<?php
/**
 * @copyright 2012 Instaclick Inc.
 */

namespace IC\Bundle\Base\TestBundle\Test\Functional;

use Doctrine\Common\DataFixtures\Purger\ORMPurger;
use IC\Bundle\Base\TestBundle\Test\Loader;
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

    const FIXTURES_PURGE_MODE = ORMPurger::PURGE_MODE_DELETE;

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

        // Only initialize schema and fixtures if any are defined
        $fixtureList = static::getFixtureList();

        if (empty($fixtureList) && ! $this->forceSchemaLoad) {
            return;
        }

        $fixtureLoader = new Loader\FixtureLoader($this->client, static::FIXTURES_PURGE_MODE);
        $executor      = $fixtureLoader->load(static::MANAGER_NAME, $fixtureList);

        $this->referenceRepository = $executor->getReferenceRepository();

        $cacheDriver = $this->referenceRepository->getManager()->getMetadataFactory()->getCacheDriver();

        if ($cacheDriver) {
            $cacheDriver->deleteAll();
        }
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
            static::getClientOptions(),
            static::getServerParameters()
        );
    }

    /**
     * Overwritable method for client's options
     *
     * @return array
     */
    protected static function getClientOptions()
    {
        return array('environment' => static::ENVIRONMENT);
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

    /**
     * Assert HTTP response status code is 200 OK.
     *
     * @param \Symfony\Component\HttpFoundation\Response $response
     * @param string                                     $message
     */
    protected function assertResponseStatusOk($response, $message = '')
    {
        $this->assertResponseStatusCode(200, $response, $message);
    }

    /**
     * Assert HTTP response status code is Redirection 3xx.
     *
     * @param \Symfony\Component\HttpFoundation\Response $response
     * @param string                                     $message
     */
    protected function assertResponseStatusRedirection($response, $message = '')
    {
        $this->assertGreaterThanOrEqual(300, $response->getStatusCode(), $message);
        $this->assertLessThanOrEqual(399, $response->getStatusCode(), $message);
    }

    /**
     * Assert HTTP response status code is 301 Moved.
     *
     * @param \Symfony\Component\HttpFoundation\Response $response
     * @param string                                     $message
     */
    protected function assertResponseStatusMoved($response, $message = '')
    {
        $this->assertResponseStatusCode(301, $response, $message);
    }

    /**
     * Assert HTTP response status code is 302 Found.
     *
     * @param \Symfony\Component\HttpFoundation\Response $response
     * @param string                                     $message
     */
    protected function assertResponseStatusFound($response, $message = '')
    {
        $this->assertResponseStatusCode(302, $response, $message);
    }

    /**
     * Assert HTTP response status code is 404 Not Found.
     *
     * @param \Symfony\Component\HttpFoundation\Response $response
     * @param string                                     $message
     */
    protected function assertResponseStatusNotFound($response, $message = '')
    {
        $this->assertResponseStatusCode(404, $response, $message);
    }

    /**
     * Assert HTTP response status code matches the expected.
     *
     * @param integer                                    $expectedStatusCode
     * @param \Symfony\Component\HttpFoundation\Response $response
     * @param string                                     $message
     */
    protected function assertResponseStatusCode($expectedStatusCode, $response, $message = '')
    {
        $this->assertEquals($expectedStatusCode, $response->getStatusCode(), $message);
    }

    /**
     * Assert HTTP response header location matches the expected.
     *
     * @param string|null                                $expectedLocation
     * @param \Symfony\Component\HttpFoundation\Response $response
     * @param  string $message
     */
    protected function assertResponseRedirectionLocation($expectedLocation, $response, $message = '')
    {
        $this->assertEquals($expectedLocation, $response->headers->get('location'), $message);
    }
}
