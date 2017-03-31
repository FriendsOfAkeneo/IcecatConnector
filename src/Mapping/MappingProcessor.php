<?php

namespace Pim\Bundle\IcecatConnectorBundle\Mapping;

use Akeneo\Component\Batch\Item\DataInvalidItem;
use Akeneo\Component\StorageUtils\Detacher\ObjectDetacherInterface;
use Pim\Bundle\EnrichBundle\Connector\Processor\AbstractProcessor;
use Pim\Bundle\IcecatConnectorBundle\Mapping\Constraint\IgnoreFlagConstraint;
use Pim\Bundle\IcecatConnectorBundle\Mapping\Constraint\InvalidTypeConstraint;
use Pim\Component\Catalog\Repository\AttributeRepositoryInterface;
use Pim\Component\Connector\Exception\InvalidItemFromViolationsException;
use Symfony\Component\Validator\ConstraintValidatorInterface;
use Symfony\Component\Validator\ConstraintViolation;
use Symfony\Component\Validator\ConstraintViolationListInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;

/**
 * @author    JM Leroux <jean-marie.leroux@akeneo.com>
 * @copyright 2016 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class MappingProcessor extends AbstractProcessor
{
    /** @var ConstraintValidatorInterface */
    protected $validator;

    /** @var AttributeRepositoryInterface */
    protected $repository;

    /** @var ObjectDetacherInterface */
    protected $objectDetacher;

    /**
     * @param ValidatorInterface           $validator
     * @param AttributeRepositoryInterface $repository
     * @param ObjectDetacherInterface      $objectDetacher
     */
    public function __construct(
        ValidatorInterface $validator,
        AttributeRepositoryInterface $repository,
        ObjectDetacherInterface $objectDetacher
    ) {
        $this->validator = $validator;
        $this->repository = $repository;
        $this->objectDetacher = $objectDetacher;
    }

    /**
     * {@inheritdoc}
     */
    public function process($item)
    {
        $errors = $this->validator->validate($item, new IgnoreFlagConstraint());

        if (count($errors) !== 0) {
            throw new InvalidItemFromViolationsException($errors, new DataInvalidItem($item));
        }

        if (1 == $item['ignore_flag']) {
            $this->stepExecution->incrementSummaryInfo('skipped_mapping');

            return null;
        }

        $attributeCode = $item['pim_attribute_code'];
        $pimAttribute = $this->repository->findOneByIdentifier($attributeCode);
        if (null === $pimAttribute) {
            $this->stepExecution->addWarning(
                sprintf('The "%s" attribute code does not exist.', $attributeCode),
                [],
                new DataInvalidItem($item)
            );

            return null;
        }

        $errors = $this->validator->validate($item, [
            new InvalidTypeConstraint(['attribute' => $pimAttribute]),
        ]);
        if (count($errors) !== 0) {
            $this->addWarningMessage($errors, $item);
        }

        $this->objectDetacher->detach($pimAttribute);

        return [
            'feature_id'         => $item['feature_id'],
            'pim_attribute_code' => $item['pim_attribute_code'],
        ];
    }

    /**
     * @param ConstraintViolationListInterface $violations
     * @param mixed                            $item
     */
    protected function addWarningMessage(ConstraintViolationListInterface $violations, $item)
    {
        $errors = '';
        /** @var ConstraintViolation $violation */
        foreach ($violations as $violation) {
            $errors .= sprintf(
                "%s\n",
                $violation->getMessage()
            );
        }
        $this->stepExecution->addWarning($errors, [], new DataInvalidItem($item));
    }
}
