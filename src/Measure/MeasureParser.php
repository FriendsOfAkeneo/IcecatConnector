<?php

namespace Pim\Bundle\IcecatConnectorBundle\Measure;

use Pim\Bundle\IcecatConnectorBundle\Model\Measure;

/**
 * @author JM Leroux <jean-marie.leroux@akeneo.com>
 * @copyright 2016 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class MeasureParser
{
    const XPATH_DESCRIPTION = 'Descriptions/Description[@langid=1]';
    const XPATH_NAME = 'Names/Name[@langid=1]';

    public function parseNode(\SimpleXMLElement $element)
    {
        $measure = new Measure();

        $attributes = $element->attributes();
        $measure->setId((int) $attributes['ID']);
        $measure->setSign((string) $element->Sign);
        if ($element->xpath(self::XPATH_NAME)) {
            $measure->setName((string)$element->xpath(self::XPATH_NAME)[0]);
        }
        if ($element->xpath(self::XPATH_DESCRIPTION)) {
            $measure->setDescription((string) $element->xpath(self::XPATH_DESCRIPTION)[0]);
        }

        return $measure;
    }
}
