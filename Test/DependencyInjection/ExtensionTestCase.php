<?php
/**
 * @copyright 2013 Instaclick Inc.
 */

namespace IC\Bundle\Base\TestBundle\Test\DependencyInjection;

use Symfony\Component\DependencyInjection\Definition;
use Symfony\Component\HttpKernel\DependencyInjection\Extension;
use IC\Bundle\Base\TestBundle\Test\ContainerAwareTestCase;

/**
 * Dependency Injection Extension Unit test case
 *
 * @author Guilherme Blanco <guilhermeblanco@hotmail.com>
 * @author Ryan Albon <ryanalbon@gmail.com>
 * @author John Cartwright <jcartdev@gmail.com>
 */
abstract class ExtensionTestCase extends ContainerAwareTestCase
{
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
        $actual = $this->container->hasDefinition($id) ?: $this->container->hasAlias($id);

        $this->assertTrue($actual, sprintf('Expected definition "%s" to exist', $id));
    }

    /**
     * Assertion on the Constructor Arguments of a DIC Service Definition.
     *
     * @param \Symfony\Component\DependencyInjection\Definition $definition
     * @param array                                             $argumentList
     */
    public function assertDICConstructorArguments(Definition $definition, array $argumentList)
    {
        $this->assertEquals(
            $argumentList,
            $definition->getArguments(),
            sprintf(
                'Expected and actual DIC Service constructor argumentList of definition "%s" do not match.',
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
     * Assertion on the called Method of a DIC Service Definition.
     *
     * @param \Symfony\Component\DependencyInjection\Definition $definition
     * @param string                                            $methodName
     * @param array                                             $parameterList
     */
    public function assertDICDefinitionMethodCall(Definition $definition, $methodName, array $parameterList = null)
    {
        $callList    = $definition->getMethodCalls();
        $matchedCall = null;

        foreach ($callList as $call) {
            if ($call[0] === $methodName) {
                $matchedCall = $call;

                break;
            }
        }

        if ( ! $matchedCall) {
            $this->fail(
                sprintf('Method "%s" is was expected to be called.', $methodName)
            );
        }

        if ($parameterList !== null) {
            $this->assertEquals(
                $parameterList,
                $matchedCall[1],
                sprintf('Expected parameters to method "%s" do not match the actual parameters.', $methodName)
            );
        }
    }

    /**
     * Assertion on the called Method position of a DIC Service Definition.
     *
     * @param integer                                           $position
     * @param \Symfony\Component\DependencyInjection\Definition $definition
     * @param string                                            $methodName
     * @param array                                             $parameterList
     */
    public function assertDICDefinitionMethodCallAt($position, Definition $definition, $methodName, array $parameterList = null)
    {
        $callList = $definition->getMethodCalls();

        if ( ! isset($callList[$position][0])) {
            // Throws an Exception
            $this->fail(
                sprintf('Method "%s" is expected to be called at position %s.', $methodName, $position)
            );
        }

        $this->assertEquals(
            $methodName,
            $callList[$position][0],
            sprintf('Method "%s" is expected to be called at position %s.', $methodName, $position)
        );

        if ($parameterList !== null) {
            $this->assertEquals(
                $parameterList,
                $callList[$position][1],
                sprintf('Expected parameters to methods "%s" do not match the actual parameters.', $methodName)
            );
        }
    }

    /**
     * Assertion on the Definition that a method is not called for a DIC Service Definition.
     *
     * @param \Symfony\Component\DependencyInjection\Definition $definition
     * @param string                                            $methodName
     */
    public function assertDICDefinitionMethodNotCalled(Definition $definition, $methodName)
    {
        $callList = $definition->getMethodCalls();

        foreach ($callList as $call) {
            if ($call[0] === $methodName) {
                $this->fail(
                    sprintf('Method "%s" is not expected to be called.', $methodName)
                );
            }
        }
    }
}
