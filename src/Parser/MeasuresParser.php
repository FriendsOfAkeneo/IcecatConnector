<?php

namespace Pim\Bundle\IcecatConnectorBundle\Parser;

use Pim\Bundle\IcecatConnectorBundle\Model\Measure;

class MeasuresParser
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

    public function writeCsv(Measure $measure, $output)
    {
        $csv = sprintf('%d;%s;%s;%s' . PHP_EOL,
            $measure->getId(),
            $measure->getName(),
            $measure->getSign(),
            $measure->getDescription()
        );
        file_put_contents($output, $csv, FILE_APPEND);
    }
}
