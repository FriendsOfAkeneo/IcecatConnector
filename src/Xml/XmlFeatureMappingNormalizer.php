<?php

namespace Pim\Bundle\IcecatConnectorBundle\Xml;

use Akeneo\Component\Batch\Item\ItemProcessorInterface;
use SimpleXMLElement;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;

/**
 * Convert an Icecat XML feature string into array
 *
 * @author    JM Leroux <jean-marie.leroux@akeneo.com>
 * @copyright 2016 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class XmlFeatureMappingNormalizer implements NormalizerInterface, ItemProcessorInterface
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
        if (!$xmlFeature instanceof SimpleXMLElement) {
            return null;
        }

        $attributes = $xmlFeature->attributes();
        $result = [
            'feature_id'   => (int) $attributes['ID'],
            'feature_type' => (string) $attributes['Type'],
        ];

        $result['pim_attribute_code'] = sprintf('icecat_%d', $result['feature_id']);
        $result['ignore_flag'] = 0;

        if ($xmlFeature->xpath(self::XPATH_NAME)) {
            $result['feature_name'] = (string) $xmlFeature->xpath(self::XPATH_NAME)[0];
        }
        if ($xmlFeature->xpath(self::XPATH_DESCRIPTION)) {
            $result['feature_description'] = (string) $xmlFeature->xpath(self::XPATH_DESCRIPTION)[0];
        }

        $measure = $xmlFeature->Measure;
        if ($measure) {
            $result['feature_unit'] = (string) $measure->attributes()['Sign'];
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

    /**
     * {@inheritdoc}
     */
    public function process($item)
    {
        return $this->normalize($item);
    }
}
