<?php

namespace Icecat\Mapper;

use Pim\Component\Catalog\AttributeTypes;

/**
 * @author    JM Leroux <jean-marie.leroux@akeneo.com>
 * @copyright 2016 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class AttributeTypeMapper
{
    public function getPimMType($icecatType, $icecateMeasure)
    {
        $icecatType = strtolower($icecatType);
        if ('dropdown' === $icecatType) {
            return AttributeTypes::OPTION_SIMPLE_SELECT;
        } elseif ('multi_dropdown' === $icecatType) {
            return AttributeTypes::OPTION_MULTI_SELECT;
        } elseif ('y_n' === $icecatType) {
            return AttributeTypes::BOOLEAN;
        } elseif ('y_n_o' === $icecatType) {
            return AttributeTypes::BOOLEAN;
        } elseif ('numerical' === $icecatType) {
            return AttributeTypes::NUMBER;
        }
    }
}
