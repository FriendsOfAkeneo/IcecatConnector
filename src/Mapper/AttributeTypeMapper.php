<?php

namespace Pim\Bundle\IcecatConnectorBundle\Mapper;

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
    /** @var MeasureMapper */
    protected $measureMappper;

    /**
     * @param MeasureMapper $measureMappper
     */
    public function __construct(MeasureMapper $measureMappper)
    {
        $this->measureMappper = $measureMappper;
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
            if (null !== $this->resolveUnit($icecatFeature)) {
                return AttributeTypes::METRIC;
            }

            return AttributeTypes::NUMBER;
        } elseif ('range' === $type) {
            if (null !== $this->resolveUnit($icecatFeature)) {
                return AttributeTypes::METRIC; //range metric
            }

            return AttributeTypes::NUMBER; // range
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
     * @param $unit
     *
     * @return array
     */
    protected function resolveUnit($unit)
    {
        try {
            return $this->measureMappper->resolvePimMeasure($unit);
    } catch (\Exception $e) {
            return null;
        }
    }
}
