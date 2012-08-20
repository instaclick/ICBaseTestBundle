# InstaClick Base Test Bundle

*IMPORTANT NOTICE:* This bundle is still under development. Any changes will
be done without prior notice to users that consume this package. Of course this
code will become stable at a certain point, but for now, use at your own risk.

## Introduction

This bundle provides lower level support for functional tests on Symfony2.
Through the concept of helpers and loaders, this bundle supports individual
support for test types, such as Command, Controller, Service, Validator, etc.

This bundle requires at least that you are using Symfony 2.1.

## Installation

Installing this bundle can be done through these simple steps:

    1. Add this bundle to your project as a composer dependency:

        // composer.json
        {
            // ...
            require: {
                // ...
                "instaclick/base-test-bundle": "dev-master"
            }
        }

    2. Add this bundle in your application kernel:

        // application/ApplicationKernel.php
        public function registerBundles()
        {
            // ...
            if (in_array($this->getEnvironment(), array('test'))) {
                $bundles[] = new IC\Bundle\Base\TestBundle\ICBaseTestBundle();
            );

            return $bundles;
        }

    3. Double check if your session name is configured correctly:

        # application/config/config_test.yml
        framework:
            test: ~
            session:
                name: "myapp"

## Creating your first functional test

Just like a Symfony2 test, implementing a functional test is easy:

        use IC\Bundle\Base\TestBundle\Test\WebTestCase;

        class MyFunctionalTest extends WebTestCase
        {
            public function testSomething()
            {
                // Normal test here. You can benefit from an already initialized
                // Symfony2 Client by using directly $this->getClient()
            }
        }

## Functional tests that requires Database to be initialized

When building your functional tests, it is recurrent that you want your test
Database to be created and populated with initial information. This bundle
comes with a native support for Doctrine Data Fixtures, which allows you to
load your database information before your test is actually executed.

To enable your schema to be initialized and also load the initial Database
information, just implements the protected static method `getFixtureList`:

        /**
         * {@inheritdoc}
         */
        protected static function getFixtureList()
        {
            return array(
                'Me\MyBundle\DataFixtures\ORM\LoadData'
            );
        }

If you don't need any fixtures to be loaded before your test, but still want
your empty Database schema to be loaded, you can tell the TestCase to still
force the schema to be loaded by changing the configuration protperty flag:

        protected $forceSchemaLoad = true;

## Overriding the default client instance

Some applications require more granular control what Symfony2 Client can do.
This bundle allows you to change the default client initialization, just like
you normally do with your Symfony2 WebTestCase, by overriding the static method
`createClient`.

### Changing Client environment

This bundle allows you to easily change the environment the Client gets
initialized. To change the default environment (default: "test"), just redefine
the constant `ENVIRONMENT`:

        const ENVIRONMENT = "default";

### Changing default Object Manager name

When using Databases, you may want to change the default ObjectManager your
test should run against. Just like Client's environment, changing the default
ObjectManager only requires to redefine the constant `MANAGER_NAME`:

        const MANAGER_NAME = "stats";

### Server authentication

Whenever your application uses HTTP authentication, your test should still have
an ability to test secured pages. With simplicity in mind, Client can be
initialized in an authenticated state for HTTP. THe only required step is
implement the protected static method `getServerParameters`:

        /**
         * {@inheritdoc}
         */
        protected static function getServerParameters()
        {
            return array(
                'PHP_AUTH_USER' => 'admin',
                'PHP_AUTH_PW'   => 'jed1*passw0rd'
            );
        }

### Changing Client initialization

Most of the times, overriding `createClient` is enough. Whenever you need more
refined support, you can still override the default Client initialization by
overriding the protected static method `initializeClient` (sample is actually
the default implementation of the method):

        /**
         * {@inheritdoc}
         */
        protected static function initializeClient()
        {
            return self::createClient(
                array('environment' => static::ENVIRONMENT),
                self::getServerParameters()
            );
        }

## Useful hints

### Creating a class MockBuilder

Instead of using the native API of PHPUnit, WebTestCase provides a useful
method right at your hands:

        public function testFoo()
        {
            $myMock = $this->getClassMock('My\Foo');

            $myMock->expects($this->any())
                   ->method('bar')
                   ->will($this->returnValue(true));

            // ...
        }

### Retrieving the Service Container

Symfony Client holds an instance of Service Container. You can retrieve the
container instance can be retrieved directly from the client:

        public function testFooService()
        {
            $container = $this->getClient()->getContainer();

            // ...
        }

### Retrieving Database references

Database dependant applications usually forces you to fetch for elements before
actually testing consuming them. WebTestCase takes advantage of Doctrine Data
Fixtures package, allowing you to retrieve references without requiring a
database fetch.

        pubic function testIndex()
        {
            $credential = $this->getReferenceRepository()->getReference('core.security.credential#admin');

            // ...
        }

## Database dependant functional tests

Most cases, your application relies on a database to work. To help you on this
task, and also speed up the execution of your suite, we strongly suggest that
you use SQLite as your test database.
The reason why do that is because this database works around a single file,
allowing you to easily create isolated scenarios. Also, this bundle has an
ability to cache the generated schema and reuse it for every test.
Another functionality this bundle also integrates natively is Doctrine Data
Fixtures, allowing your SQLite test database to be cached with common - test
agnostic - information even before your actual test gets executed.

Finally, but not less important, if you use SQLite, your test is gonna run
faster, even faster with all this native support it is built-in by the usage of
this bundle.

To use SQLite as your test database, add this to your `app/config_test.yml`:

        doctrine:
            dbal:
                default_connection: default
                connections:
                    default:
                        driver:   pdo_sqlite
                        path:     %kernel.cache_dir%/test.db

**Attention: you need to use Doctrine >= 2.2 to benefit from this feature.**

## Using Helpers

This bundle comes with built-in helpers to simplify testing individual pieces
of software. This section will explain the native ones and also how to inject
your own helper.

### Retrieving a helper

Any helper works taking advantage of Symfony2 Client instance available on
WebTestCase. All helpers are registered in the latter, and it allows you to
retrieve an instance easily by calling the method:

        public function testFoo()
        {
            $commandHelper = $this->getHelper('command');

            // ...
        }

### Available helpers

ICBaseTestBundle comes with a collection of helpers out of the box. Here is the
list of available helpers:

* Command
* Controller
* Service
* Session
* Validator

#### Command Helper

This helper is available to you under the name `command`.
It works as a wrapper around the Symfony Console Component. This also allows
you to configure your command, build the command input and retrieve the
response content by using its available API.
As an example, here is a full implementation of a command test:

        /**
         * @dataProvider provideDataForCommand
         */
        public function testCommand(array $arguments, $content)
        {
            $commandHelper = $this->getHelper('command');

            $commandHelper->setCommandName('ic:base:mail:flush-queue');
            $commandHelper->setMaxMemory(5242880); // Optional (default value: 5 * 1024 * 1024 KB)

            $input    = $commandHelper->getInput($arguments);
            $response = $commandHelper->run($input);

            $this->assertContains($content, $response);
        }

        /**
         * {@inheritdoc}
         */
        public function provideDataForCommand()
        {
            return array(
                array(array('--server' => 'mail.instaclick.com'), 'Flushed queue successfully'),
            );
        }

#### Controller Helper

This helper is available to you under the name `controller`.
The motivation of this helper is to enable sub-requests to be executed without
requiring the master request to be called. It allows to simulate a request and
check for returned content.

**IMPORTANT NOTICE:** Controller Helper is still under development. It is part
of the plan to connect Symfony\Component\DomCrawler\Crawler as a separate
method.

        public function testViewAction()
        {
            $controllerHelper = $this->getHelper('controller');
            $response         = $controllerHelper->render(
                'ICBaseGeographicalBundle:Map:view',
                array(
                    'attributes' => array(
                        'coordinate' => new Coordinate(-34.45, 45.56),
                        'width'      => 640,
                        'height'     => 480
                    )
                )
            );

            $this->assertContains('-34.45', $response);
            $this->assertContains('45.56', $response);
            $this->assertContains('640', $response);
            $this->assertContains('480', $response);
        }

#### Service Helper

This helper is available to you under the name `service`.
Whenever you want to mock a Service and automatically inject back to Service
Container, this helper is for you. Helper contains a method that does that:
`mock`. It returns you an instance of MockBuilder.

        public function testFoo()
        {
            $serviceHelper = $this->getHelper('service');

            $authenticationService = $serviceHelper->mock('core.security.authentication');

            // ...
        }

#### Session Helper

This helper is available to you under the name `session`.
Session helper was written with a simple idea in mind: allows you to simulate
login for controller tests. Of course, Session helper also allows you to
retrieve the actual Symfony Session to define/check/remove entries normally
too.

        pubic function testIndex()
        {
            $sessionHelper = $this->getHelper('session');

            $credential = $this->getReferenceRepository()->getReference('core.security.credential#admin');

            // $sessionHelper->getSession() is also available
            $sessionHelper->authenticate($credential, 'secured_area');

            // ...
        }

#### Validator Helper

This helper is available to you under the name `validator`.
Validator Helper encapsulates more logic than the other native helpers. It
allows you to also test success and error states because it requires internal
mocking of elements needed for testing.

        public function testSuccessValidate($value)
        {
            $validatorHelper = $this->getHelper('validator');
            $serviceHelper   = $this->getHelper('service');

            $validatorHelper->setValidatorClass('IC\Bundle\Base\GeographicalBundle\Validator\Constraints\ValidLocationValidator');
            $validatorHelper->setConstraintClass('IC\Bundle\Base\GeographicalBundle\Validator\Constraints\ValidLocation');

            // Required mocking
            $geolocationService = $serviceHelper->mock('base.geographical.service.geolocation');
            $geolocationService->expects($this->any())
                ->method('convertLocationToCoordinate')
                ->will($this->returnValue($this->mockCoordinate()));

            $validatorHelper->getValidator()->setGeolocationService($geolocationService);

            // Testing
            $validatorHelper->success($value);
        }

### Creating and registering your own helper

Registering a new helper is required to override the protected static method
`initializeHelperList`:

        protected static function initializeHelperList()
        {
            $helperList = parent::initializeHelperList();

            $helperList->set('my', 'IC\Bundle\Site\DemoBundle\Test\Helper\MyHelper');
            // Retrieve as: $myHelper = $this->getHelper('my');

            return $helperList;
        }