<?php

namespace spec\Pim\Bundle\IcecatConnectorBundle\Mapping;

use Akeneo\Component\Batch\Item\DataInvalidItem;
use Akeneo\Component\Batch\Model\StepExecution;
use Akeneo\Component\StorageUtils\Detacher\ObjectDetacherInterface;
use PhpSpec\ObjectBehavior;
use Pim\Bundle\IcecatConnectorBundle\Mapping\Constraint\IgnoreFlagConstraint;
use Pim\Bundle\IcecatConnectorBundle\Mapping\Constraint\InvalidTypeConstraint;
use Pim\Component\Catalog\Model\AttributeInterface;
use Pim\Component\Catalog\Repository\AttributeRepositoryInterface;
use Pim\Component\Connector\Exception\InvalidItemFromViolationsException;
use Prophecy\Argument;
use Symfony\Component\Validator\ConstraintViolation;
use Symfony\Component\Validator\ConstraintViolationList;
use Symfony\Component\Validator\Validator\ValidatorInterface;

class MappingProcessorSpec extends ObjectBehavior
{
    function let(
        ValidatorInterface $validator,
        AttributeRepositoryInterface $repository,
        ObjectDetacherInterface $objectDetacher,
        StepExecution $stepExecution
    ) {
        $this->beConstructedWith($validator, $repository, $objectDetacher);
        $this->setStepExecution($stepExecution);
    }

    function it_throws_exception_on_invalid_ignore_flag_value($validator)
    {
        $item = [
            'pim_attribute_code' => 'foo',
            'ignore_flag' => 'invalid_value',
        ];

        $constraint = new IgnoreFlagConstraint();

        $violation = new ConstraintViolation($constraint->message, $constraint->message, [], '', '', $item);
        $violations = new ConstraintViolationList([$violation]);
        $validator->validate($item, $constraint)->willReturn($violations);

        $this->shouldThrow(InvalidItemFromViolationsException::class)->during('process', [$item]);
    }

    function it_returns_null_on_ignore_flag($stepExecution)
    {
        $stepExecution->incrementSummaryInfo('skipped_mapping')->shouldBeCalled();
        $this->process(['pim_attribute_code' => 'foo', 'ignore_flag' => '1'])->shouldReturn(null);
    }

    function it_returns_null_on_unfound_attribute($repository, $stepExecution)
    {
        $item = [
            'pim_attribute_code' => 'foo',
            'ignore_flag' => '0',
        ];
        $repository->findOneByIdentifier('foo')->shouldBeCalled()->willReturn(null);
        $stepExecution->addWarning(
            sprintf('The "%s" attribute code does not exist.', 'foo'),
            [],
            new DataInvalidItem($item)
        )->shouldBeCalled();
        $this->process($item)->shouldreturn(null);
    }

    function it_returns_processed_item(
        $validator,
        $repository,
        $objectDetacher,
        $stepExecution,
        AttributeInterface $pimAttribute
    ) {
        $item = [
            'pim_attribute_code' => 'foo',
            'feature_id' => '123',
            'ignore_flag' => '0',
            'feature_type' => 'numerical',
            'feature_name' => 'baz',
        ];

        $repository->findOneByIdentifier('foo')->shouldBeCalled()->willReturn($pimAttribute);
        $validator->validate($item, Argument::type(IgnoreFlagConstraint::class))->willReturn(null);

        $constraint = new InvalidTypeConstraint(['attribute' => $pimAttribute]);
        $violation = new ConstraintViolation($constraint->message, $constraint->message, [], '', '', $item);
        $violations = new ConstraintViolationList([$violation]);
        $validator->validate($item, Argument::type('array'))->willReturn($violations);

        $stepExecution->addWarning(
            $constraint->message . "\n",
            [],
            new DataInvalidItem($item)
        )->shouldBeCalled();
        $objectDetacher->detach($pimAttribute)->shouldBeCalled();

        $this->process($item)->shouldReturn([
            'feature_id' => $item['feature_id'],
            'pim_attribute_code' => $item['pim_attribute_code'],
        ]);
        $validator->validate($item, Argument::any())->shouldHaveBeenCalledTimes(2);
    }
}
