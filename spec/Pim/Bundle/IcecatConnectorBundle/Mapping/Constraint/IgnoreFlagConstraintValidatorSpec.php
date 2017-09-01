<?php

namespace spec\Pim\Bundle\IcecatConnectorBundle\Mapping\Constraint;

use Pim\Bundle\IcecatConnectorBundle\Mapping\Constraint\IgnoreFlagConstraint;
use Pim\Bundle\IcecatConnectorBundle\Mapping\Constraint\IgnoreFlagConstraintValidator;
use PhpSpec\ObjectBehavior;
use Prophecy\Argument;
use Symfony\Component\Validator\Context\ExecutionContextInterface;
use Symfony\Component\Validator\Violation\ConstraintViolationBuilderInterface;

class IgnoreFlagConstraintValidatorSpec extends ObjectBehavior
{
    function let(ExecutionContextInterface $context)
    {
        $this->initialize($context);
    }

    function it_is_initializable()
    {
        $this->shouldHaveType(IgnoreFlagConstraintValidator::class);
    }

    function it_validates_valid_values($context)
    {
        $constraint = new IgnoreFlagConstraint();
        $context->buildViolation(Argument::any())->shouldNotBeCalled();
        $this->validate(['ignore_flag' => '0'], $constraint);
        $this->validate(['ignore_flag' => '1'], $constraint);
    }

    function it_adds_error_on_invalid_value(ConstraintViolationBuilderInterface $builder, $context)
    {
        $item = ['ignore_flag' => 'foo'];
        $constraint = new IgnoreFlagConstraint();
        $expectedMessage = sprintf($constraint->message, 'foo');
        $context->buildViolation($expectedMessage)->shouldBeCalled()->willReturn($builder);
        $builder->addViolation()->shouldBeCalled();
        $this->validate($item, $constraint);
    }
}
