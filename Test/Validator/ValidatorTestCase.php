<?php
/**
 * @copyright 2013 Instaclick Inc.
 */

namespace IC\Bundle\Base\TestBundle\Test\Validator;

use Symfony\Component\Validator\ConstraintValidator;
use Symfony\Component\Validator\Constraint;
use IC\Bundle\Base\TestBundle\Test\TestCase;

/**
 * Validator Unit test case.
 *
 * @author Guilherme Blanco <guilhermeblanco@hotmail.com>
 */
abstract class ValidatorTestCase extends TestCase
{
    /**
     * Assertion of a valid value over a Validator
     *
     * @param \Symfony\Component\Validator\ConstraintValidator $validator
     * @param \Symfony\Component\Validator\Constraint          $constraint
     * @param mixed                                            $value
     */
    public function assertValid(ConstraintValidator $validator, Constraint $constraint, $value)
    {
        $context    = $this->getExecutionContextMock();
        $methodName = method_exists('Symfony\Component\Validator\ExecutionContext', 'addViolationAt')
            ? 'addViolationAt'
            : 'addViolationAtSubPath';

        $context
             ->expects($this->never())
             ->method('addViolation');

        $context
             ->expects($this->never())
             ->method('addViolationAtPath');

        $context
             ->expects($this->never())
             ->method($methodName);

        $validator->initialize($context);
        $validator->validate($value, $constraint);
    }

    /**
     * Assertion of an error message of an invalid value over a Validator
     *
     * @param \Symfony\Component\Validator\ConstraintValidator $validator
     * @param \Symfony\Component\Validator\Constraint          $constraint
     * @param mixed                                            $value
     * @param string                                           $message
     * @param array                                            $parameters
     */
    public function assertInvalid(ConstraintValidator $validator, Constraint $constraint, $value, $message, array $parameters = array())
    {
        $context = $this->getExecutionContextMock();

        $context
             ->expects($this->once())
             ->method('addViolation')
             ->with($message, $parameters);

        $validator->initialize($context);
        $validator->validate($value, $constraint);
    }

    /**
     * Assertion of an error message of an invalid value on a path over a Validator
     *
     * @param \Symfony\Component\Validator\ConstraintValidator $validator
     * @param \Symfony\Component\Validator\Constraint          $constraint
     * @param mixed                                            $value
     * @param string                                           $type
     * @param string                                           $message
     * @param array                                            $parameters
     */
    public function assertInvalidAtPath(ConstraintValidator $validator, Constraint $constraint, $value, $type, $message, array $parameters = array())
    {
        $context = $this->getExecutionContextMock();

        $context
             ->expects($this->once())
             ->method('addViolationAtPath')
             ->with($type, $message, $parameters);

        $validator->initialize($context);
        $validator->validate($value, $constraint);
    }

    /**
     * Assertion of an error message of an invalid value on a sub-path over a Validator
     *
     * @param \Symfony\Component\Validator\ConstraintValidator $validator
     * @param \Symfony\Component\Validator\Constraint          $constraint
     * @param mixed                                            $value
     * @param string                                           $type
     * @param string                                           $message
     * @param array                                            $parameters
     */
    public function assertInvalidAtSubPath(ConstraintValidator $validator, Constraint $constraint, $value, $type, $message, array $parameters = array())
    {
        $context    = $this->getExecutionContextMock();
        $methodName = method_exists('Symfony\Component\Validator\ExecutionContext', 'addViolationAt')
            ? 'addViolationAt'
            : 'addViolationAtSubPath';

        $context
             ->expects($this->once())
             ->method($methodName)
             ->with($type, $message, $parameters);

        $validator->initialize($context);
        $validator->validate($value, $constraint);
    }

    /**
     * Retrieve a mocked instance of Validator Execution Context.
     *
     * @return \Symfony\Component\Validator\ExecutionContext
     */
    private function getExecutionContextMock()
    {
        return $this->getMockBuilder('Symfony\Component\Validator\ExecutionContext')
            ->disableOriginalConstructor()
            ->getMock();
    }
}
