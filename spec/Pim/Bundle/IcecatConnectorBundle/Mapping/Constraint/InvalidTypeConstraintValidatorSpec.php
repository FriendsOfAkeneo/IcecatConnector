<?php

namespace spec\Pim\Bundle\IcecatConnectorBundle\Mapping\Constraint;

use PhpSpec\ObjectBehavior;
use Pim\Bundle\IcecatConnectorBundle\Mapping\Constraint\InvalidTypeConstraint;
use Pim\Bundle\IcecatConnectorBundle\Mapping\Constraint\InvalidTypeConstraintValidator;
use Pim\Component\Catalog\AttributeTypes;
use Pim\Component\Catalog\Model\AttributeInterface;
use Prophecy\Argument;
use Symfony\Component\Validator\Context\ExecutionContextInterface;
use Symfony\Component\Validator\Violation\ConstraintViolationBuilderInterface;

class InvalidTypeConstraintValidatorSpec extends ObjectBehavior
{
    function let(ExecutionContextInterface $context)
    {
        $this->initialize($context);
    }

    function it_is_initializable()
    {
        $this->shouldHaveType(InvalidTypeConstraintValidator::class);
    }

    function it_validates_valid_simple_types(
        $context,
        AttributeInterface $attribute
    )
    {
        $mappings = [
            AttributeTypes::TEXT => [
                '',
                '2d',
                '3d',
                'alphanumeric',
                'contrast ratio',
                'ratio',
                'text',
                'range',
            ],
            AttributeTypes::TEXTAREA => [
                'textarea',
            ],
            AttributeTypes::BOOLEAN => [
                'y_n',
            ],
            AttributeTypes::OPTION_SIMPLE_SELECT => [
                'y_n_o',
                'dropdown',
                'multi_dropdown',
            ],
        ];


        foreach ($mappings as $attributeType => $featureTypes) {

            $attribute->getType()->willReturn($attributeType);
            $constraint = new InvalidTypeConstraint(['attribute' => $attribute->getWrappedObject()]);

            foreach ($featureTypes as $featureType) {
                $context->buildViolation(Argument::any())->shouldNotBeCalled();
                $this->validate(
                    [
                        'feature_type' => $featureType,
                        'feature_unit' => null,
                    ],
                    $constraint
                );
            }
        }
    }

    function it_validates_number_type(
        $context,
        AttributeInterface $attribute
    ) {
        $attribute->getType()->willReturn(AttributeTypes::NUMBER);
        $constraint = new InvalidTypeConstraint(['attribute' => $attribute->getWrappedObject()]);

        $context->buildViolation(Argument::any())->shouldNotBeCalled();
        $item = [
            'feature_type' => 'numerical',
            'feature_unit' => null,
        ];
        $this->validate($item, $constraint);
    }

    function it_validates_metric_type(
        $context,
        AttributeInterface $attribute
    )
    {
        $attribute->getType()->willReturn(AttributeTypes::METRIC);
        $constraint = new InvalidTypeConstraint(['attribute' => $attribute->getWrappedObject()]);

        $context->buildViolation(Argument::any())->shouldNotBeCalled();
        $item = [
            'feature_type' => 'numerical',
            'feature_unit' => 'mm',
        ];
        $this->validate($item, $constraint);
    }

    function it_throw_exception_on_unknown_type(AttributeInterface $attribute)
    {
        $attribute->getType()->willReturn(AttributeTypes::TEXT);
        $constraint = new InvalidTypeConstraint(['attribute' => $attribute->getWrappedObject()]);

        $item = [
            'feature_id' => 12345,
            'feature_type' => 'unknowntype',
            'feature_unit' => ''
        ];

        $exception = new \InvalidArgumentException(
            sprintf(
                'Unresolvable type %s for feature %s',
                $item['feature_type'],
                $item['feature_id']
            )
        );

        $this->shouldThrow($exception)->during('validate', [$item, $constraint]);
    }

    function it_does_not_validate_invalid_type(
        AttributeInterface $attribute,
        ConstraintViolationBuilderInterface $violationBuilder,
        $context
    )
    {
        $item = [
            'feature_id' => 12345,
            'feature_type' => 'numerical',
            'feature_unit' => 'mm',
        ];
        $attribute->getCode()->willReturn('code');
        $attribute->getType()->willReturn(AttributeTypes::NUMBER);
        $constraint = new InvalidTypeConstraint(['attribute' => $attribute->getWrappedObject()]);

        $msg = sprintf(
            $constraint->message,
            12345,
            $item['feature_type'],
            'code',
            AttributeTypes::NUMBER,
            AttributeTypes::METRIC
        );

        $context->buildViolation($msg)->willReturn($violationBuilder);
        $violationBuilder->addViolation()->shouldBeCalled();


        $this->validate($item, $constraint);
    }
}
