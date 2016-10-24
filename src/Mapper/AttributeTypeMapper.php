<?php

namespace Pim\Bundle\IcecatConnectorBundle\Mapper;

use Pim\Bundle\ExtendedAttributeTypeBundle\AttributeType\RangeType;
use Pim\Bundle\ExtendedMeasureBundle\Exception\UnknownUnitException;
use Pim\Bundle\ExtendedMeasureBundle\Exception\UnresolvableUnitException;
use Pim\Bundle\ExtendedMeasureBundle\Repository\MeasureRepositoryInterface;
use Pim\Bundle\IcecatConnectorBundle\Exception\UnresolvableTypeException;
use Pim\Bundle\IcecatConnectorBundle\Model\Feature;
use Pim\Component\Catalog\AttributeTypes;

/**
 * @author    JM Leroux <jean-marie.leroux@akeneo.com>
 * @copyright 2016 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class AttributeTypeMapper
{
    /**
     * @var MeasureRepositoryInterface
     */
    private $measureRepository;

    /**
     * @param MeasureRepositoryInterface $repository
     */
    public function __construct(MeasureRepositoryInterface $repository)
    {
        $this->measureRepository = $repository;
    }

    /**
     * @param Feature $icecatFeature
     *
     * @return string
     */
    public function resolvePimType(Feature $icecatFeature)
    {
        $type = strtolower($icecatFeature->getType());

        if ('dropdown' === $type) {
            return AttributeTypes::OPTION_SIMPLE_SELECT;
        } elseif ('multi_dropdown' === $type) {
            return AttributeTypes::OPTION_MULTI_SELECT;
        } elseif ('y_n' === $type) {
            return AttributeTypes::BOOLEAN;
        } elseif ('y_n_o' === $type) {
            return AttributeTypes::BOOLEAN;
        } elseif ('numerical' === $type) {
            if (null !== $this->resolveByUnit($icecatFeature)) {
                return AttributeTypes::METRIC;
            }

            return AttributeTypes::NUMBER;
        } elseif ('range' === $type) {
            if (null !== $this->resolveByUnit($icecatFeature)) {
                return AttributeTypes::METRIC; //range metric
            }

            return RangeType::TYPE_RANGE;
        } elseif ('ratio' === $type || 'contrast ratio' === $type) {
            return AttributeTypes::NUMBER;
        } elseif ('2d' === $type || '3d' === $type) {
            return AttributeTypes::TEXT;
        } elseif ('text' === $type || 'textarea' === $type || 'alphanumeric' === $type) {
            return AttributeTypes::TEXT;
        } elseif ('' === $type) {
            return AttributeTypes::TEXT;
        }

        throw new UnresolvableTypeException($icecatFeature);
    }

    /**
     * Get attribute
     *
     * @param Feature $icecatFeature
     *
     * @return string|null
     */
    protected function resolveByUnit(Feature $icecatFeature)
    {
        $attributeType = $this->getMappedAttributeType($icecatFeature);

        if (null === $attributeType) {
            $unit = $icecatFeature->getMeasureSign();
            try {
                return $this->measureRepository->findByUnit($unit);
            } catch (UnresolvableUnitException $e) {
                return null;
            } catch (UnknownUnitException $e) {
                return null;
            }
        }

        return $attributeType;
    }

    /**
     * @param Feature $icecatFeature
     *
     * @return array
     */
    protected function getMappedAttributeType(Feature $icecatFeature)
    {
        if ('x' === $icecatFeature->getMeasureSign()) {
            return AttributeTypes::OPTION_SIMPLE_SELECT; // CD speed
        }

        return null;
    }
}
