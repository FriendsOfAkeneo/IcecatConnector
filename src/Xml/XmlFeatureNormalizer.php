<?php

namespace Pim\Bundle\IcecatConnectorBundle\Xml;

use SimpleXMLElement;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;

/**
 * Convert an Icecat XML feature string into array
 *
 * @author    JM Leroux <jean-marie.leroux@akeneo.com>
 * @copyright 2016 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class XmlFeatureNormalizer implements NormalizerInterface
{
    const XPATH_DESCRIPTION = 'Descriptions/Description[@langid=1]';
    const XPATH_NAME = 'Names/Name[@langid=1]';
    const XPATH_SIGN = 'Measure/Signs/Sign[@langid=1]';

    /**
     * {@inheritdoc}
     *
     * @param SimpleXMLElement $xmlFeature object to normalize
     */
    public function normalize($xmlFeature, $format = null, array $context = [])
    {
        $attributes = $xmlFeature->attributes();
        $result = [
            'id' => (int) $attributes['ID'],
            'type' => (string) $attributes['Type'],
        ];

        if ($xmlFeature->xpath(self::XPATH_NAME)) {
            $result['name'] = (string) $xmlFeature->xpath(self::XPATH_NAME)[0];
        }
        if ($xmlFeature->xpath(self::XPATH_DESCRIPTION)) {
            $result['description'] = (string) $xmlFeature->xpath(self::XPATH_DESCRIPTION)[0];
        }

        $measure = $xmlFeature->Measure;
        if ($measure) {
            $result['sign'] = (string) $measure->attributes()['Sign'];
        }

        return $result;
    }

    /**
     * {@inheritdoc}
     */
    public function supportsNormalization($data, $format = null)
    {
        return $data instanceof SimpleXMLElement;
    }
}
