<?php

namespace Pim\Bundle\IcecatConnectorBundle\Xml;

use Oro\Bundle\ConfigBundle\Config\ConfigManager;
use Pim\Bundle\IcecatConnectorBundle\Mapping\AttributeMapper;
use Symfony\Component\Serializer\Encoder\DecoderInterface;

/**
 * Decode an Icecat XML product string into an Akeneo standard array format
 *
 * @author    JM Leroux <jean-marie.leroux@akeneo.com>
 * @copyright 2016 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class XmlProductDecoder implements DecoderInterface
{
    /** @var string */
    protected $scope;

    /** @var AttributeMapper */
    protected $attributeMapper;

    /** @var ConfigManager */
    protected $configManager;

    /**
     * @param ConfigManager   $configManager
     * @param AttributeMapper $attributeMapper
     * @param string          $scope
     *
     * @internal param ConfigManager $configManager
     */
    public function __construct(ConfigManager $configManager, AttributeMapper $attributeMapper, $scope)
    {
        $this->scope = $scope;
        $this->attributeMapper = $attributeMapper;
        $this->configManager = $configManager;
    }

    /**
     * {@inheritdoc}
     */
    public function decode($xmlString, $format, array $context = [])
    {
        $standardItem = [];

        try {
            $simpleXmlNode = simplexml_load_string($xmlString);
            $icecatProduct = $simpleXmlNode->Product;

            $pimAttributeCode = $this->configManager->get('pim_icecat_connector.description');
            if (!empty($pimAttributeCode)) {
                $standardItem = $this->addProductValue(
                    $standardItem,
                    $pimAttributeCode,
                    (string) $icecatProduct->ProductDescription->attributes()['LongDesc']
                );
            }

            $pimAttributeCode = $this->configManager->get('pim_icecat_connector.short_description');
            if (!empty($pimAttributeCode)) {
                $standardItem = $this->addProductValue(
                    $standardItem,
                    $pimAttributeCode,
                    (string) $icecatProduct->ProductDescription->attributes()['ShortDesc']
                );
            }

            $pimAttributeCode = $this->configManager->get('pim_icecat_connector.summary_description');
            if (!empty($pimAttributeCode)) {
                $standardItem = $this->addProductValue(
                    $standardItem,
                    $pimAttributeCode,
                    (string) $icecatProduct->SummaryDescription->LongSummaryDescription
                );
            }

            $pimAttributeCode = $this->configManager->get('pim_icecat_connector.short_summary_description');
            if (!empty($pimAttributeCode)) {
                $standardItem = $this->addProductValue(
                    $standardItem,
                    $pimAttributeCode,
                    (string) $icecatProduct->SummaryDescription->ShortSummaryDescription
                );
            }

            foreach ($icecatProduct->ProductFeature as $xmlFeature) {
                $featureId = (int) $xmlFeature->Feature->attributes()['ID'];
                $pimCode = $this->attributeMapper->getMapped($featureId);
                if (!empty($pimCode)) {
                    $value = (string) $xmlFeature->LocalValue->attributes()['Value'];
                    $standardItem = $this->addProductValue(
                        $standardItem,
                        $pimCode,
                        $value
                    );
                }
            }
        } catch (\Exception $e) {
            throw new XmlDecodeException(sprintf('XML decode error for string %s', $xmlString), 0, $e);
        }

        dump($standardItem);

        return $standardItem;
    }

    protected function addProductValue(array $standardItem, $pimCode, $value)
    {
        $standardItem[$pimCode] = [
            [
                'data'   => $value,
                'locale' => 'en_US',
                'scope'  => $this->scope,
            ],
        ];

        return $standardItem;
    }

    /**
     * {@inheritdoc}
     */
    public function supportsDecoding($format)
    {
        return 'xml' === $format;
    }
}
