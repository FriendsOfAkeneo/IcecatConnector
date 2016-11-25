<?php

namespace Pim\Bundle\IcecatConnectorBundle\Xml;

use Pim\Bundle\IcecatConnectorBundle\Mapping\AttributeMapper;
use Pim\Bundle\IcecatConnectorBundle\Mapping\MapperInterface;
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
    /** @var MapperInterface */
    protected $mapper;

    /** @var string */
    protected $scope;

    /**
     * @param string $scope
     */
    public function __construct($scope)
    {
        $this->mapper = new AttributeMapper();
        $this->scope = $scope;
    }

    /**
     * {@inheritdoc}
     */
    public function decode($xmlString, $format, array $context = [])
    {
        $standardItem = [];

        $simpleXmlNode = simplexml_load_string($xmlString);
        $icecatProduct = $simpleXmlNode->Product;

        $standardItem = $this->addProductValue(
            $standardItem,
            'product_description',
            (string) $icecatProduct->ProductDescription->attributes()['LongDesc']
        );
        $standardItem = $this->addProductValue(
            $standardItem,
            'long_description',
            (string) $icecatProduct->SummaryDescription->LongSummaryDescription
        );
        $standardItem = $this->addProductValue(
            $standardItem,
            'short_description',
            (string) $icecatProduct->SummaryDescription->ShortSummaryDescription
        );

        foreach ($icecatProduct->ProductFeature as $xmlFeature) {
            $featureId = (int) $xmlFeature->Feature->attributes()['ID'];
            $value = (string) $xmlFeature->LocalValue->attributes()['Value'];
            $standardItem = $this->addProductValue(
                $standardItem,
                $featureId,
                $value
            );
        }

        dump($standardItem);

        return $standardItem;
    }

    protected function addProductValue(array $standardItem, $sourceItem, $value)
    {
        $pimCode = $this->mapper->getMapped($sourceItem);

        if (null === $pimCode) {
            return $standardItem;
        }
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
