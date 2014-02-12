<?php
/**
 * @copyright 2012 Instaclick Inc.
 */

namespace IC\Bundle\Base\TestBundle\Test\Helper;

use Symfony\Component\Validator\ConstraintValidator;
use Symfony\Component\Validator\Constraint;
use IC\Bundle\Base\TestBundle\Test\Functional\WebTestCase;

/**
 * Validator helper class.
 *
 * @author Guilherme Blanco <guilhermeblanco@hotmail.com>
 */
class ValidatorHelper extends AbstractHelper
{
    /**
     * @var \Symfony\Component\Validator\ExecutionContext
     */
    private $context;

    /**
     * @var string
     */
    private $validatorClass;

    /**
     * @var \Symfony\Component\Validator\ConstraintValidator
     */
    private $validator;

    /**
     * @var string
     */
    private $constraintClass;

    /**
     * @var \Symfony\Component\Validator\Constraint
     */
    private $constraint;

    /**
     * {@inheritdoc}
     */
    public function __construct(WebTestCase $testCase)
    {
        parent::__construct($testCase);

        $this->context = $testCase->getClassMock('Symfony\Component\Validator\ExecutionContext');
    }

    /**
     * Define the execution context.
     *
     * @param \Symfony\Component\Validator\ExecutionContext $context
     */
    public function setExecutionContext($context)
    {
        $this->context = $context;
    }

    /**
     * Define the constraint validator class name.
     *
     * @param string $validatorClass
     */
    public function setValidatorClass($validatorClass)
    {
        $this->validatorClass = $validatorClass;
    }

    /**
     * Define the constraint validator.
     *
     * @param \Symfony\Component\Validator\ConstraintValidator $validator
     */
    public function setValidator(ConstraintValidator $validator = null)
    {
        $this->validator = $validator;
    }

    /**
     * Retrieve the constraint validator.
     *
     * @return \Symfony\Component\Validator\ConstraintValidator
     */
    public function getValidator()
    {
        if ( ! $this->validator) {
            $this->validator = $this->initializeValidator();
        }

        return $this->validator;
    }

    /**
     * Define the constraint class name.
     *
     * @param string $constraintClass
     */
    public function setConstraintClass($constraintClass)
    {
        $this->constraintClass = $constraintClass;
    }

    /**
     * Define the constraint.
     *
     * @param \Symfony\Component\Validator\Constraint $constraint
     */
    public function setConstraint(Constraint $constraint = null)
    {
        $this->constraint = $constraint;
    }

    /**
     * Retrieve the constraint.
     *
     * @return \Symfony\Component\Validator\Constraint
     */
    public function getConstraint()
    {
        if ( ! $this->constraint) {
            $this->constraint = $this->initializeConstraint();
        }

        return $this->constraint;
    }

    /**
     * Execute the test in success mode.
     *
     * @param mixed     $value
     */
    public function success($value)
    {
        $validator  = $this->getValidator();
        $constraint = $this->getConstraint();

        $this->context
             ->expects($this->testCase->never())
             ->method('addViolation');

        $this->context
             ->expects($this->testCase->never())
             ->method('addViolationAtPath');

        $this->context
             ->expects($this->testCase->never())
             ->method('addViolationAtSubPath');

        $this->context
             ->expects($this->testCase->never())
             ->method('addViolationAt');

        $validator->validate($value, $constraint);
    }

    /**
     * Execute the test in failure mode at own/current path.
     *
     * @param mixed  $value      Value
     * @param string $message    Message
     * @param array  $parameters Parameters
     */
    public function failure($value, $message, array $parameters = array())
    {
        $validator  = $this->getValidator();
        $constraint = $this->getConstraint();

        $this->context
             ->expects($this->testCase->once())
             ->method('addViolation')
             ->with($message, $parameters);

        $validator->validate($value, $constraint);
    }

    /**
     * Execute the test in failure mode at full path.
     *
     * @param mixed  $value      Value
     * @param string $type       Type
     * @param string $message    Message
     * @param array  $parameters Paramenters
     */
    public function failureAtPath($value, $type, $message, array $parameters = array())
    {
        $validator  = $this->getValidator();
        $constraint = $this->getConstraint();

        $this->context
             ->expects($this->testCase->once())
             ->method('addViolationAtPath')
             ->with($type, $message, $parameters);

        $validator->validate($value, $constraint);
    }

    /**
     * Execute the test in failure mode at sub path.
     *
     * @param mixed  $value      Value
     * @param string $type       Type
     * @param string $message    Message
     * @param array  $parameters Parameters
     */
    public function failureAtSubPath($value, $type, $message, array $parameters = array())
    {
        $validator  = $this->getValidator();
        $constraint = $this->getConstraint();

        $methodName = method_exists('Symfony\Component\Validator\ExecutionContext', 'addViolationAt')
            ? 'addViolationAt'
            : 'addViolationAtSubPath';

        $this->context
             ->expects($this->testCase->once())
             ->method($methodName)
             ->with($type, $message, $parameters);

        $validator->validate($value, $constraint);
    }

    /**
     * Initialize a constraint validator.
     *
     * @return \Symfony\Component\Validator\ConstraintValidator
     */
    private function initializeValidator()
    {
        $validatorClass = $this->validatorClass;
        $validator      = new $validatorClass();

        $validator->initialize($this->context);

        return $validator;
    }

    /**
     * Initialize a constraint.
     *
     * @return \Symfony\Component\Validator\Constraint
     */
    private function initializeConstraint()
    {
        $constraintClass = $this->constraintClass;
        $constraint      = new $constraintClass();

        return $constraint;
    }
}
