<?php
/**
 * @copyright 2013 Instaclick Inc.
 */

namespace IC\Bundle\Base\TestBundle\Test\Helper\Unit;

/**
 * Function helper class.
 *
 * @author Anthon Pang <apang@softwaredevelopment.ca>
 */
class FunctionHelper extends UnitHelper
{
    public static $functions;

    /**
     * Mock a function
     *
     * @param string   $methodName
     * @param callable $function
     * @param string   $namespace
     *
     * @throws \Exception
     *
     * @return mixed
     */
    public function mock($methodName, $function = null, $namespace = null)
    {
        // eval() protection
        if ( ! preg_match('/^[A-Za-z0-9_]+$/D', $methodName)
            || ($namespace && ! preg_match('/^[A-Za-z0-9_]+$/D', $namespace))
        ) {
            throw new \Exception('Invalid method name and/or namespace');
        }

        // namespace guesser
        if ($namespace === null) {
            $caller = $this->getCaller();

            if ( ! $caller || ! isset($caller[0]) || ($pos = strrpos($caller[0], '\\')) === false) {
                throw new \Exception('Unable to mock functions in the root namespace');
            }

            $namespace = str_replace(array('\\Test\\', '\\Tests\\'), '\\', substr($caller[0], 0, $pos));
        }

        if ( ! function_exists('\\' . $namespace . '\\' . $methodName)) {
            eval(<<<END_OF_MOCK
namespace $namespace;

function $methodName()
{
    if (\IC\Bundle\Base\TestBundle\Test\Helper\Unit\FunctionHelper::hasMock('$methodName')) {
        return call_user_func_array(
            array('IC\Bundle\Base\TestBundle\Test\Helper\Unit\FunctionHelper', 'invoke'),
            array('$methodName', func_get_args())
        );
    }

    call_user_func_array('$methodName', func_get_args());
}
END_OF_MOCK
            );

        }

        if (is_null($function) || is_scalar($function)) {
            $function = function () use ($function) {
                return $function;
            };
        }

        if (is_object($function) && preg_match('/^Mock_FunctionProxy_[0-9a-f]+$/', get_class($function))) {
            $function = array($function, 'invoke');
        }

        self::$functions[$methodName] = $function;
    }

    /**
     * Create a mock object as proxy to a function
     *
     * @return mixed
     */
    public function createMock()
    {
        $mock = $this->testCase->createMock('IC\Bundle\Base\TestBundle\Test\Dummy\FunctionProxy');

        return $mock;
    }

    /**
     * Invoke function
     *
     * @return mixed
     */
    public static function invoke()
    {
        $args       = func_get_args();
        $methodName = array_shift($args);

        $callable = isset(self::$functions[$methodName])
            ? self::$functions[$methodName]
            : $methodName;

        return call_user_func_array($callable, $args);
    }

    /**
     * Has mock?
     *
     * @param string $methodName
     *
     * @return boolean
     */
    public static function hasMock($methodName)
    {
        return array_key_exists($methodName, self::$functions);
    }

    /**
     * Cleanup
     */
    public function cleanUp()
    {
        self::$functions = array();
    }

    /**
     * Get caller
     *
     * @return array|null
     */
    private function getCaller()
    {
        $trace = debug_backtrace();

        // the first two lines in the call stack are getCaller and mockFunction
        if (isset($trace[2])) {
            $class    = isset($trace[2]['class']) ? $trace[2]['class'] : null;
            $function = isset($trace[2]['function']) ? $trace[2]['function'] : null;

            return array($class, $function);
        }
    }
}
