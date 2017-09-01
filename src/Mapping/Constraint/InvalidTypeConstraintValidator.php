<?php

namespace Pim\Bundle\IcecatConnectorBundle\Mapping\Constraint;

use Pim\Component\Catalog\AttributeTypes;
use Pim\Component\Catalog\Model\AttributeInterface;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;

/**
 * @author    JM Leroux <jean-marie.leroux@akeneo.com>
 * @copyright 2016 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class InvalidTypeConstraintValidator extends ConstraintValidator
{
    /**
     * {@inheritdoc}
     */
    public function validate($item, Constraint $constraint)
    {
        $this->validateAttributeType($item, $constraint);
    }

    /**
     * {@inheritdoc}
     */
    protected function validateAttributeType($item, InvalidTypeConstraint $constraint)
    {
        $pimAttribute = $constraint->getAttribute();
        $attributeType = $pimAttribute->getType();
        $featureType = trim($item['feature_type']);
        $featureUnit = trim($item['feature_unit']);

        $typesSimpleMapping = [
            ''                => AttributeTypes::TEXT,
            '2d'              => AttributeTypes::TEXT,
            '3d'              => AttributeTypes::TEXT,
            'alphanumeric'    => AttributeTypes::TEXT,
            'contrast ratio'  => AttributeTypes::TEXT,
            'ratio'           => AttributeTypes::TEXT,
            'text'            => AttributeTypes::TEXT,
            'textarea'        => AttributeTypes::TEXTAREA,
            'y_n'             => AttributeTypes::BOOLEAN,
            'y_n_o'           => AttributeTypes::OPTION_SIMPLE_SELECT,
            'dropdown'        => AttributeTypes::OPTION_SIMPLE_SELECT,
            'multi_dropdown'  => AttributeTypes::OPTION_SIMPLE_SELECT,
            // @todo change this last one
            'range'           => AttributeTypes::TEXT,
        ];

        $expectedType = null;
        if (array_key_exists($featureType, $typesSimpleMapping)) {
            $expectedType = $typesSimpleMapping[$featureType];
        } elseif ('numerical' === $featureType && empty($featureUnit)) {
            $expectedType = AttributeTypes::NUMBER;
        } elseif ('numerical' === $featureType) {
            $expectedType = AttributeTypes::METRIC;
        }

        if (null === $expectedType) {
            throw new \InvalidArgumentException(sprintf('Unresolvable type %s for feature %s', $item['feature_type'], $item['feature_id']));
        }

        if ($expectedType !== $attributeType) {
            $this->addViolation($constraint, $pimAttribute, $item, $expectedType);
        }
    }

    protected function addViolation(InvalidTypeConstraint $constraint, AttributeInterface $pimAttribute, $item, $expectedType)
    {
        $featureId = $item['feature_id'];
        $featureType = $item['feature_type'];
        $pimAttributeCode = $pimAttribute->getCode();
        $pimAttributeType = $pimAttribute->getType();
        $message = sprintf($constraint->message, $featureId, $featureType, $pimAttributeCode, $pimAttributeType, $expectedType);
        $this->context->buildViolation($message)->addViolation();
    }
}
