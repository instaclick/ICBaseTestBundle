<?php
/**
 * @copyright 2013 Instaclick Inc.
 */

namespace IC\Bundle\Base\TestBundle\Test\DependencyInjection;

use Symfony\Component\DependencyInjection\Definition;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\HttpKernel\DependencyInjection\Extension;
use IC\Bundle\Base\TestBundle\Test\TestCase;

/**
 * Dependency Injection Extension Unit test case
 *
 * @author Guilherme Blanco <guilhermeblanco@hotmail.com>
 */
abstract class ExtensionTestCase extends TestCase
{
    /**
     * @var \Symfony\Component\DependencyInjection\ContainerBuilder
     */
    protected $container;

    /**
     * {@inheritdoc}
     */
    public function setUp()
    {
        parent::setUp();

        $this->container = new ContainerBuilder();
    }

    /**
     * {@inheritdoc}
     */
    public function tearDown()
    {
        unset($this->container);

        parent::tearDown();
    }

    /**
     * Loads the configuration into a provided Extension.
     *
     * @param \Symfony\Component\HttpKernel\DependencyInjection\Extension $extension
     * @param array                                                       $configuration
     */
    protected function load(Extension $extension, array $configuration)
    {
        $extension->load(array($configuration), $this->container);
    }

    /**
     * Assertion on the alias of a Container Builder.
     *
     * @param string $expected
     * @param string $key
     */
    public function assertAlias($expected, $key)
    {
        $alias = (string) $this->container->getAlias($key);

        $this->assertEquals($expected, $alias, sprintf('%s alias is correct', $key));
    }

    /**
     * Assertion on the parameter of a Container Builder.
     *
     * @param string $expected
     * @param string $key
     */
    public function assertParameter($expected, $key)
    {
        $parameter = $this->container->getParameter($key);

        $this->assertEquals($expected, $parameter, sprintf('%s parameter is correct', $key));
    }

    /**
     * Assertion on the Definition existance of a Container Builder.
     *
     * @param string $id
     */
    public function assertHasDefinition($id)
    {
        $actual = $this->container->hasDefinition($id) ?: $this->containerBuilder->hasAlias($id);

        $this->assertTrue($actual);
    }

    /**
     * Assertion on the Constructor Arguments of a DIC Service Definition.
     *
     * @param \Symfony\Component\DependencyInjection\Definition $definition
     * @param array                                             $arguments
     */
    public function assertDICConstructorArguments(Definition $definition, array $arguments)
    {
        $this->assertEquals(
            $arguments,
            $definition->getArguments(),
            sprintf(
                'Expected and actual DIC Service constructor arguments of definition "%s" do not match.',
                $definition->getClass()
            )
        );
    }

    /**
     * Assertion on the Class of a DIC Service Definition.
     *
     * @param \Symfony\Component\DependencyInjection\Definition $definition
     * @param string                                            $expectedClass
     */
    public function assertDICDefinitionClass(Definition $definition, $expectedClass)
    {
        $this->assertEquals(
            $expectedClass,
            $definition->getClass(),
            "Expected Class of the DIC Container Service Definition is wrong."
        );
    }

    /**
     * Assertion on the called Method position of a DIC Service Definition.
     *
     * @param integer                                           $position
     * @param \Symfony\Component\DependencyInjection\Definition $definition
     * @param string                                            $methodName
     * @param array                                             $params
     */
    public function assertDICDefinitionMethodCallAt($position, Definition $definition, $methodName, array $params = null)
    {
        $calls = $definition->getMethodCalls();

        if ( ! isset($calls[$position][0])) {
            // Throws an Exception
            $this->fail(
                sprintf('Method "%s" is expected to be called at position %s.', $methodName, $position)
            );
        }

        $this->assertEquals(
            $methodName,
            $calls[$position][0],
            sprintf('Method "%s" is expected to be called at position %s.', $methodName, $position)
        );

        if ($params !== null) {
            $this->assertEquals(
                $params,
                $calls[$position][1],
                sprintf('Expected parameters to methods "%s" do not match the actual parameters.', $methodName)
            );
        }
    }
}