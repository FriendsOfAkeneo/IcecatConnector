<?php

namespace Pim\Bundle\IcecatConnectorBundle\Mapper;

use Pim\Bundle\ExtendedMeasureBundle\Exception\UnknownUnitException;
use Pim\Bundle\ExtendedMeasureBundle\Exception\UnresolvableUnitException;
use Pim\Bundle\ExtendedMeasureBundle\Resolver\MeasureResolverInterface;
use Pim\Bundle\IcecatConnectorBundle\Model\Feature;
use Pim\Component\Catalog\AttributeTypes;

/**
 * @author    JM Leroux <jean-marie.leroux@akeneo.com>
 * @copyright 2016 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class MeasureMapper
{
    /** @var MeasureResolverInterface */
    protected $measureResolver;

    /**
     * @param MeasureResolverInterface $measureResolver
     */
    public function __construct(MeasureResolverInterface $measureResolver)
    {
        $this->measureResolver = $measureResolver;
    }

    /**
     * @param Feature $icecatFeature
     *
     * @return string
     */
    public function resolvePimMeasure(Feature $icecatFeature)
    {
        $measure = $this->getMappedMeasure($icecatFeature);

        if (null === $measure) {
            $measure = $this->resolveUnit($icecatFeature->getMeasureSign());
        }

        return $measure;
    }

    /**
     * @param Feature $icecatFeature
     *
     * @return array
     */
    protected function getMappedMeasure(Feature $icecatFeature)
    {
        if ('x' === $icecatFeature->getMeasureSign()) {
            return AttributeTypes::NUMBER; // CD speed
        }

        return null;
    }

    /**
     * @param $unit
     *
     * @return array
     */
    protected function resolveUnit($unit)
    {
        try {
            return $this->measureResolver->resolvePimMeasure($unit);
        } catch (UnresolvableUnitException $e) {
            return null;
        } catch (UnknownUnitException $e) {
            return null;
        }
    }
}
