# InstaClick Base Test Bundle

*IMPORTANT NOTICE:* This bundle is still under development. Any changes will
be done without prior notice to consumers of this package. Of course this
code will become stable at a certain point, but for now, use at your own risk.

## Introduction

This bundle provides lower level support for unit and functional tests on
Symfony2. Through the concept of helpers and loaders, this bundle supports
individual support for test types, such as Command, Controller, Service,
Validator, etc.

This bundle requires that you are using, at least, Symfony 2.1.

## Installation

Installing this bundle can be done through these simple steps:

1. Add this bundle to your project as a composer dependency:
```javascript
    // composer.json
    {
        // ...
        require-dev: {
            // ...
            "instaclick/base-test-bundle": "dev-master"
        }
    }
```

2. Add this bundle in your application kernel:
```php
    // application/ApplicationKernel.php
    public function registerBundles()
    {
        // ...
        if (in_array($this->getEnvironment(), array('test'))) {
            $bundles[] = new IC\Bundle\Base\TestBundle\ICBaseTestBundle();
        }

        return $bundles;
    }
```

3. Double check if your session name is configured correctly:
```yaml
# application/config/config_test.yml
    framework:
        test: ~
        session:
            name: "myapp"
```

## Unit Testing

By default, Symfony2 does not provide a native customized support for unit test
creation. To mitigate this problem, this bundle contains a wide set of basic
unit test abstraction to help you with this job.

### Protected/Private

There may be times where you want to directly test a protected/private method
or access a non-public property (and the class lacks a getter or setter).
For example, the call chain from the closest public method is sufficiently
long to make testing an arduous task.

To overcome this obstacle, TestCase provides some methods to assist you.

Let's say this is your subject under test:

```php
class Foo
{
    protected $bar;

    private function getBar()
    {
        return $this->bar;
    }
}
```

Here is an example:

```php
use IC\Bundle\Base\TestBundle\Test\TestCase;

class ICFooBarBundleTest extends TestCase
{
    public function testGetBar()
    {
        $subject = new Foo;
        $expected = 'Hello';

        $this->setPropertyOnObject($subject, 'bar', $expected);

        $method = $this->makeCallable($subject, 'getBar');

        $this->assertEquals($expected, $method->invoke($subject));
    }
}
``` 

### Bundle testing

Most people do not even think about testing a bundle initialization. This is a
bad concept, because every line of code deserves to be tested, even though you
may not have manually created a class.

Bundle classes are known to be the place to register your CompilerPass
instances. No matter if you have a CompilerPass or not, it is a good practice
to create a default test for your Bundle class.
Here is an example on how to achieve it:

```php
use IC\Bundle\Base\TestBundle\Test\BundleTestCase;
use IC\Bundle\Base\MailBundle\ICBaseMailBundle;

class ICBaseMailBundleTest extends BundleTestCase
{
    public function testBuild()
    {
        $bundle = new ICBaseMailBundle();

        $bundle->build($this->container);

        // Add your tests here
    }
}
```

### Dependency Injection

#### Configuration testing

Just like Bundle classes, Configuration classes are very easy to overlook when
testing. Testing this specific test is a good approach because it validates
your line of thought with Bundle configuration normalization of parameters or
even configuration default values. ICBaseTestBundle already provides a small
class that can help you with this task.

```php
use IC\Bundle\Base\TestBundle\Test\DependencyInjection\ConfigurationTestCase;
use IC\Bundle\Base\MailBundle\DependencyInjection\Configuration;

class ConfigurationTest extends ConfigurationTestCase
{
    public function testDefaults()
    {
        $configuration = $this->processConfiguration(new Configuration(), array());

        $this->assertEquals('INBOX', $configuration['mail_bounce']['mailbox']);
    }

    // ...
}
```

#### Extension testing

Testing the DependencyInjection Extension helps you to validate your service
definitions and container configuration.
Helpful methods available to you:
* `assertAlias($expected, $key)`
* `assertParameter($expected, $key)`
* `assertHasDefinition($id)`
* `assertDICConstructorArguments(Definition $definition, array $arguments)`
* `assertDICDefinitionClass(Definition $definition, $expectedClass)`
* `assertDICDefinitionMethodCallAt($position, Definition $definition, $methodName, array $params = null)`

```php
use IC\Bundle\Base\TestBundle\Test\DependencyInjection\ExtensionTestCase;
use IC\Bundle\Base\MailBundle\DependencyInjection\ICBaseMailExtension;

class ICBaseMailExtensionTest extends ExtensionTestCase
{
    /**
     * @expectedException \Symfony\Component\Config\Definition\Exception\InvalidConfigurationException
     */
    public function testInvalidConfiguration()
    {
        $extension     = new ICBaseMailExtension();
        $configuration = array();

        $this->load($extension, $configuration);
    }

    public function testValidConfiguration()
    {
        $this->createFullConfiguration();

        $this->assertParameter('John Smith', 'ic_base_mail.composer.default_sender.name');

        $this->assertDICConstructorArguments(
            $this->container->getDefinition('ic_base_mail.service.composer'),
            array()
        );
        $this->assertDICConstructorArguments(
            $this->container->getDefinition('ic_base_mail.service.sender'),
            array()
        );
        $this->assertDICConstructorArguments(
            $this->container->getDefinition('ic_base_mail.service.bounce_mail'),
            array()
        );
    }

    // ...
}
```

### Validator testing

Validators are a key part of the system, because it helps you verify your
business rules are being respected. Testing them becomes even more crucial.
Constraints can generate violations at different locations. In order to help
you verify it assigns it at the correct place, `ValidatorTestCase` provides you
a set of methods:
* `assertValid(ConstraintValidator $validator, Constraint $constraint, $value)`
* `assertInvalid(ConstraintValidator $validator, Constraint $constraint, $value, $message, array $parameters = array())`
* `assertInvalidAtPath(ConstraintValidator $validator, Constraint $constraint, $value, $type, $message, array $parameters = array())`
* `assertInvalidAtSubPath(ConstraintValidator $validator, Constraint $constraint, $value, $type, $message, array $parameters = array())`

```php
use MyBundle\Validator\Constraints;
use IC\Bundle\Base\TestBundle\Test\Validator\ValidatorTestCase;

class BannedEmailValidatorTest extends ValidatorTestCase
{
    public function testValid()
    {
        $validator  = new Constraints\BannedEmailValidator();
        $constraint = new Constraints\BannedEmail();
        $value      = 'email@domain.com';

        $this->assertValid($validator, $constraint, $value);
    }

    public function testInvalid()
    {
        $validator  = new Constraints\BannedEmailValidator();
        $constraint = new Constraints\BannedEmail();
        $value      = 'domain.com';
        $message    = 'Please provide a valid email.';
        $parameters = array();

        $this->assertInvalid($validator, $constraint, $value, $message, $parameters);
    }
}
```

## Creating your first functional test

Just like a Symfony2 test, implementing a functional test is easy:

```php
use IC\Bundle\Base\TestBundle\Test\Functional\WebTestCase;

class MyFunctionalTest extends WebTestCase
{
    public function testSomething()
    {
        // Normal test here. You can benefit from an already initialized
        // Symfony2 Client by using directly $this->getClient()
    }
}
```

## Functional tests that requires Database to be initialized

When building your functional tests, it is recurrent that you want your test
Database to be created and populated with initial information. This bundle
comes with native support for Doctrine Data Fixtures, which allows you to
load your database information before your test is actually executed.

To enable your schema to be initialized and also load the initial Database
information, just implement the protected static method `getFixtureList`:

```php
/**
 * {@inheritdoc}
 */
protected static function getFixtureList()
{
    return array(
        'Me\MyBundle\DataFixtures\ORM\LoadData'
    );
}
```

If you don't need any fixtures to be loaded before your test, but still want
your empty Database schema to be loaded, you can tell the TestCase to still
force the schema to be loaded by changing the configuration property flag:

```php
protected $forceSchemaLoad = true;
```

## Overriding the default client instance

Some applications require more granular control than what Symfony2 Client can do.
This bundle allows you to change the default client initialization, just like
you normally do with your Symfony2 WebTestCase, by overriding the static method
`createClient`.

### Changing Client environment

This bundle allows you to easily change the environment the Client gets
initialized. To change the default environment (default: "test"), just redefine
the constant `ENVIRONMENT`:

```php
const ENVIRONMENT = "default";
```

### Changing default Object Manager name

When using Databases, you may want to change the default ObjectManager your
test should run against. Just like Client's environment, changing the default
ObjectManager only requires you to redefine the constant `MANAGER_NAME`:

```php
const MANAGER_NAME = "stats";
```

### Server authentication

Whenever your application uses HTTP authentication, your test should still have
an ability to test secured pages. With simplicity in mind, Client can be
initialized in an authenticated state for HTTP. The only required step is
implement the protected static method `getServerParameters`:

```php
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
```

Note: this assumes you have enabled `http_basic` in your security configuration using this setting in the `config_test.yml` file:

```yaml
# app/config/config_test.yml
security:
    firewalls:
        your_firewall_name:
            http_basic:
```

See [How to simulate HTTP Authentication in a Functional Test](http://symfony.com/doc/current/cookbook/testing/http_authentication.html) for details 

### Changing Client initialization

Oftentimes, overriding `createClient` is enough. Whenever you need more
refined support, you can still override the default Client initialization by
overriding the protected static method `initializeClient` (sample is actually
the default implementation of the method):

```php
/**
 * {@inheritdoc}
 */
protected static function initializeClient()
{
    return static::createClient(
        array('environment' => static::ENVIRONMENT),
        static::getServerParameters()
    );
}
```

## Useful hints

### Creating a class MockBuilder

Instead of using the native API of PHPUnit, WebTestCase provides a useful
method right at your hands:

```php
public function testFoo()
{
    $myMock = $this->getClassMock('My\Foo');

    $myMock->expects($this->any())
           ->method('bar')
           ->will($this->returnValue(true));

    // ...
}
```

### Retrieving the Service Container

Symfony Client holds an instance of Service Container. You can retrieve the
container instance directly from the client:

```php
public function testFooService()
{
    $container = $this->getClient()->getContainer();

    // ...
}
```

### Retrieving Database references

Database dependant applications usually force you to fetch for elements before
actually testing/consuming them. WebTestCase takes advantage of Doctrine Data
Fixtures package, allowing you to retrieve references without requiring a
database fetch.

```php
public function testIndex()
{
    $credential = $this->getReferenceRepository()->getReference('core.security.credential#admin');

    // ...
}
```

## Database dependant functional tests

In most cases, your application relies on a database to work. To help you on this
task, and also speed up the execution of your suite, we strongly suggest that
you use SQLite as your test database.
The reason why to do that is because this database works around a single file,
allowing you to easily create isolated scenarios. Also, this bundle has an
ability to cache the generated schema and reuse it for every test.
Another piece of functionality: this bundle integrates natively with Doctrine Data
Fixtures, allowing your SQLite test database to be cached with common - test
agnostic - information even before your actual test gets executed.

Finally, but no less important, if you use SQLite, your test will run
faster with all the native support built-in, simply by using this bundle.

To use SQLite as your test database, add this to your `app/config_test.yml`:

```yaml
doctrine:
    dbal:
        default_connection: default
        connections:
            default:
                driver:   pdo_sqlite
                path:     %kernel.cache_dir%/test.db
```

**Attention: you need to use Doctrine >= 2.2 to benefit from this feature.**

## Using Helpers

This bundle comes with built-in helpers to simplify testing individual pieces
of software. This section will explain the native ones and also how to inject
your own helper.

### Retrieving a helper

Access helpers by taking advantage of the Symfony2 Client instance available in
WebTestCase. All helpers are registered in the latter, and it allows you to
retrieve an instance easily by calling the method:

```php
public function testFoo()
{
    $commandHelper = $this->getHelper('command');

    // ...
}
```

### Available helpers

ICBaseTestBundle comes with a collection of helpers out of the box. Here is the
list of available helpers:

* Command
* Controller
* Persistence
* Route
* Service
* Session
* Validator
* Unit/Entity
* Unit/Function

#### Command Helper

This helper is available to you under the name `command`.
It works as a wrapper around the Symfony Console Component. This also allows
you to configure your command, build the command input and retrieve the
response content by using its available API.
As an example, here is a full implementation of a command test:

```php
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
```

#### Controller Helper

This helper is available to you under the name `controller`.
The motivation of this helper is to enable sub-requests to be executed without
requiring the master request to be called. It allows you to simulate a request and
check for returned content.

**IMPORTANT NOTICE:** Controller Helper is still under development. It is part
of the plan to connect Symfony\Component\DomCrawler\Crawler as a separate
method.

```php
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
```

#### Persistence Helper

This helper is available to you under the name `persistence`.
The persistence helper transforms a reference key to a reference object,
or a list of reference keys to a list of reference objects.

```php
public function testFoo()
{
    $persistenceHelper = $this->getHelper('persistence');

    $credentialList = $persistenceHelper->transformToReference(
        array(
            'core.security.credential#admin',
            'core.security.credential#user',
        )
    );

    ...
}
```

#### Route Helper

This helper is available to you under the name `route`.
The Route helper provides a method to retrieve a generated route from a route id.
Moreover, if the route is not registered, the test is marked as skipped.

```php
/**
 * @dataProvider provideRouteData
 */
public function testRoute($routeId, $parameters)
{
    $routeHelper = $this->getHelper('route');

    $route = $routeHelper->getRoute($routeId, $parameters, $absolute = false);

    ...
}
```

#### Service Helper

This helper is available to you under the name `service`.
Whenever you want to mock a Service and automatically inject it back into Service
Container, this helper is for you. Helper contains a method that does that:
`mock`. It returns an instance of MockBuilder.

```php
public function testFoo()
{
    $serviceHelper = $this->getHelper('service');

    $authenticationService = $serviceHelper->mock('core.security.authentication');

    // ...
}
```

#### Session Helper

This helper is available to you under the name `session`.
Session helper was written with a simple idea in mind: allow you to simulate
login for controller tests. Of course, Session helper also allows you to
retrieve the actual Symfony Session to define/check/remove entries normally
too.

```php
pubic function testIndex()
{
    $sessionHelper = $this->getHelper('session');

    $credential = $this->getReferenceRepository()->getReference('core.security.credential#admin');

    // $sessionHelper->getSession() is also available
    $sessionHelper->authenticate($credential, 'secured_area');

    // ...
}
```

#### Validator Helper

This helper is available to you under the name `validator`.
Validator Helper encapsulates more logic than the other native helpers. It
allows you to also test success and error states because it requires internal
mocking of elements needed for testing.

```php
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
```

#### Unit/Entity Helper

This helper is available to you under the name `Unit/Entity`.
The Unit/Entity helper helps to create Entity stubs where there is no setId() method.

```php
public function testFoo()
{
    $entityHelper = $this->getHelper('Unit/Entity');

    $entity = $entityHelper->createMock('IC\Bundle\Base\GeographicalBundle\Entity\Country', 'us');

    ...
}
```

#### Unit/Function Helper

This helper is available to you under the name `Unit/Function`.
The Unit/Function helper helps to mock built-in PHP functions.  Note: the subject under test must
be a namespaced class.

```php
public function testFoo()
{
    $functionHelper = $this->getHelper('Unit/Function');

    // mock ftp_open() to return null (default)
    $functionHelper->mock('ftp_open');

    // mock ftp_open() to return true
    $functionHelper->mock('ftp_open', true);

    // mock ftp_open() with callable
    $functionHelper->mock('ftp_open', function () { return null; });

    // mock ftp_open() with a mock object; note: the method is always 'invoke'
    $fopenProxy = $functionHelper->createMock();
    $fopenProxy->expects($this->once())
               ->method('invoke')
               ->will($this->returnValue(true));

    $functionHelper->mock('ftp_open', $fopenProxy);

    ...
}
```

### Creating and registering your own helper

Registering a new helper is required to override the protected static method
`initializeHelperList`:

```php
protected static function initializeHelperList()
{
    $helperList = parent::initializeHelperList();

    $helperList->set('my', 'IC\Bundle\Site\DemoBundle\Test\Helper\MyHelper');
    // Retrieve as: $myHelper = $this->getHelper('my');

    return $helperList;
}
```
