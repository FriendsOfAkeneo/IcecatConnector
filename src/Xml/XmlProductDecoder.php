<?php

namespace Pim\Bundle\IcecatConnectorBundle\Xml;

use Akeneo\Component\StorageUtils\Detacher\ObjectDetacherInterface;
use Oro\Bundle\ConfigBundle\Config\ConfigManager;
use Pim\Bundle\CatalogBundle\Entity\AttributeOption;
use Pim\Bundle\ExtendedMeasureBundle\Repository\MeasureRepositoryInterface;
use Pim\Bundle\IcecatConnectorBundle\Exception\MapperException;
use Pim\Bundle\IcecatConnectorBundle\Mapping\AttributeMapper;
use Pim\Component\Catalog\AttributeTypes;
use Pim\Component\Catalog\Model\AttributeInterface;
use Pim\Component\Catalog\Repository\AttributeRepositoryInterface;
use Pim\Component\Catalog\Updater\Remover\RemoverInterface;
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
    /** @var AttributeMapper */
    protected $attributeMapper;

    /** @var ConfigManager */
    protected $configManager;

    /** @var AttributeRepositoryInterface */
    protected $attributeRepository;

    /** @var string */
    protected $locale;

    /** @var string */
    protected $scope;

    /** @var MeasureRepositoryInterface */
    protected $measureRepository;

    /**
     * @param ConfigManager                $configManager
     * @param AttributeMapper              $attributeMapper
     * @param AttributeRepositoryInterface $attributeRepository
     * @param MeasureRepositoryInterface   $extendedMeasureRepository
     * @param string                       $scope
     * @param string                       $locale
     *
     * @internal param ConfigManager $configManager
     */
    public function __construct(
        ConfigManager $configManager,
        AttributeMapper $attributeMapper,
        AttributeRepositoryInterface $attributeRepository,
        MeasureRepositoryInterface $extendedMeasureRepository,
        $scope,
        $locale
    ) {
        $this->scope = $scope;
        $this->attributeMapper = $attributeMapper;
        $this->configManager = $configManager;
        $this->attributeRepository = $attributeRepository;
        $this->locale = $locale;
        $this->measureRepository = $extendedMeasureRepository;
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
                    (string) $icecatProduct->ProductDescription->attributes()['LongDesc'],
                    null
                );
            }

            $pimAttributeCode = $this->configManager->get('pim_icecat_connector.short_description');
            if (!empty($pimAttributeCode)) {
                $standardItem = $this->addProductValue(
                    $standardItem,
                    $pimAttributeCode,
                    (string) $icecatProduct->ProductDescription->attributes()['ShortDesc'],
                    null
                );
            }

            $pimAttributeCode = $this->configManager->get('pim_icecat_connector.summary_description');
            if (!empty($pimAttributeCode)) {
                $standardItem = $this->addProductValue(
                    $standardItem,
                    $pimAttributeCode,
                    (string) $icecatProduct->SummaryDescription->LongSummaryDescription,
                    null
                );
            }

            $pimAttributeCode = $this->configManager->get('pim_icecat_connector.short_summary_description');
            if (!empty($pimAttributeCode)) {
                $standardItem = $this->addProductValue(
                    $standardItem,
                    $pimAttributeCode,
                    (string) $icecatProduct->SummaryDescription->ShortSummaryDescription,
                    null
                );
            }

            foreach ($icecatProduct->ProductFeature as $xmlFeature) {
                $featureId = (int) $xmlFeature->Feature->attributes()['ID'];
                $pimCode = $this->attributeMapper->getMapped($featureId);
                if (!empty($pimCode)) {
                    $value = (string) $xmlFeature->LocalValue->attributes()['Value'];
                    $unit = (string) $xmlFeature->LocalValue->Measure->Signs->Sign;
                    $standardItem = $this->addProductValue(
                        $standardItem,
                        $pimCode,
                        $value,
                        $unit
                    );
                }
            }

            $pictures = [];
            foreach ($icecatProduct->ProductGallery as $xmlPicture) {
                if (count($xmlPicture->ProductPicture) > 0) {
                    $url = (string) $xmlPicture->ProductPicture->attributes()['Original'];
                    if ('' !== trim($url)) {
                        $pictures[] = $url;
                    }
                }
            }
            $pimAttributeCode = $this->configManager->get('pim_icecat_connector.pictures');
            $standardItem = $this->addProductValue(
                $standardItem,
                $pimAttributeCode,
                json_encode(array_values($pictures)),
                null
            );
        } catch (MapperException $e) {
            throw $e;
        } catch (\Exception $e) {
            throw new XmlDecodeException(sprintf('XML decode error for string %s', $xmlString), 0, $e);
        }

        return $standardItem;
    }

    /**
     * @param array  $standardItem
     * @param string $pimCode
     * @param mixed  $value
     * @param string $unit
     *
     * @return array
     */
    protected function addProductValue(array $standardItem, $pimCode, $value, $unit)
    {
        /** @var AttributeInterface $pimAttribute */
        $pimAttribute = $this->attributeRepository->findOneByIdentifier($pimCode);

        if (null === $pimAttribute) {
            return $standardItem;
        }

        $locale = null;
        if ($pimAttribute->isLocalizable()) {
            $locale = $this->locale;
        }

        $scope = null;
        if ($pimAttribute->isScopable()) {
            $scope = $this->scope;
        }

        if (AttributeTypes::METRIC === $pimAttribute->getType() && null !== $unit) {
            $value = $this->formatMetricValue($value, $unit);
        } elseif (AttributeTypes::PRICE_COLLECTION === $pimAttribute->getType() && null !== $unit) {
            $value = $this->formatPriceValue($value, $unit);
        } else {
            $value = $this->findOptionCode($pimAttribute, $value);
        }

        $standardItem['values'][$pimCode] = [
            [
                'data'   => $value,
                'locale' => $locale,
                'scope'  => $scope,
            ],
        ];

        return $standardItem;
    }

    /**
     * @param string $icecatValue
     * @param string $icecatUnit
     *
     * @return array
     */
    protected function formatMetricValue($icecatValue, $icecatUnit)
    {
        $measure = $this->measureRepository->findBySymbol($icecatUnit);
        return [
            'data' => $icecatValue,
            'unit' => $measure['unit']
        ];
    }

    /**
     * @param string $icecatValue
     * @param string $icecatUnit
     *
     * @return array
     */
    protected function formatPriceValue($icecatValue, $icecatUnit)
    {
        return [[
            'data' => $icecatValue,
            'currency' => $icecatUnit,
        ]];
    }

    protected function findOptionCode(AttributeInterface $pimAttribute, $icecatValue)
    {
        foreach ($pimAttribute->getOptions() as $option) {
            /** @var AttributeOption $option */
            $option->setLocale($this->locale);
            $optionValue = $option->getOptionValue()->getValue();
            if ($optionValue == $icecatValue) {
                return $option->getCode();
            }
        }

        return $icecatValue;
    }

    /**
     * {@inheritdoc}
     */
    public function supportsDecoding($format)
    {
        return 'xml' === $format;
    }
}
