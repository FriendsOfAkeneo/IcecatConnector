<?php

namespace Pim\Bundle\IcecatConnectorBundle\Mapping;

/**
 * @author JM Leroux <jean-marie.leroux@akeneo.com>
 * @copyright 2016 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
interface MapperInterface
{
    /**
     * @param $sourceItem
     *
     * @return string Target PIM entity code (attribute code for example)
     */
    public function getMapped($sourceItem);
}
