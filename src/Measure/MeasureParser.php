<?php

namespace Pim\Bundle\IcecatConnectorBundle\Measure;

use Pim\Bundle\IcecatConnectorBundle\Model\Measure;

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
