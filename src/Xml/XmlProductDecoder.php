<?php

namespace Pim\Bundle\IcecatConnectorBundle\Xml;

use Oro\Bundle\ConfigBundle\Config\ConfigManager;
use Pim\Bundle\CatalogBundle\Entity\AttributeOption;
use Pim\Bundle\ExtendedMeasureBundle\Repository\MeasureRepositoryInterface;
use Pim\Bundle\IcecatConnectorBundle\Exception\MapperException;
use Pim\Bundle\IcecatConnectorBundle\Mapping\AttributeMapper;
use Pim\Component\Catalog\AttributeTypes;
use Pim\Component\Catalog\Model\AttributeInterface;
use Pim\Component\Catalog\Repository\AttributeRepositoryInterface;
use SimpleXMLElement;
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
    protected $scope;

    /** @var MeasureRepositoryInterface */
    protected $measureRepository;

    /** @var string */
    protected $fallbackLocale;

    /**
     * @param ConfigManager                $configManager
     * @param AttributeMapper              $attributeMapper
     * @param AttributeRepositoryInterface $attributeRepository
     * @param MeasureRepositoryInterface   $extendedMeasureRepository
     * @param string                       $scope
     */
    public function __construct(
        ConfigManager $configManager,
        AttributeMapper $attributeMapper,
        AttributeRepositoryInterface $attributeRepository,
        MeasureRepositoryInterface $extendedMeasureRepository,
        $scope
    ) {
        $this->scope = $scope;
        $this->attributeMapper = $attributeMapper;
        $this->configManager = $configManager;
        $this->attributeRepository = $attributeRepository;
        $this->measureRepository = $extendedMeasureRepository;
    }

    /**
     * {@inheritdoc}
     */
    public function decode($xmlString, $format, array $context = [])
    {
        $standardItem = [];
        $icecatProduct = null;
        $locale = $context['locale'];
        $this->fallbackLocale = $context['fallback_locale'];

        try {
            $simpleXmlNode = simplexml_load_string($xmlString);
            $icecatProduct = $simpleXmlNode->Product;

            if ($icecatProduct->ProductDescription) {
                $pimAttributeCode = $this->configManager->get('pim_icecat_connector.description');
                if (!empty($pimAttributeCode)) {
                    $standardItem = $this->addProductValue(
                        $standardItem,
                        $locale,
                        $pimAttributeCode,
                        null,
                        (string)$icecatProduct->ProductDescription->attributes()['LongDesc'],
                        null
                    );
                }

                $pimAttributeCode = $this->configManager->get('pim_icecat_connector.short_description');
                if (!empty($pimAttributeCode)) {
                    $standardItem = $this->addProductValue(
                        $standardItem,
                        $locale,
                        $pimAttributeCode,
                        null,
                        (string)$icecatProduct->ProductDescription->attributes()['ShortDesc'],
                        null
                    );
                }
            }

            if ($icecatProduct->SummaryDescription) {
                $pimAttributeCode = $this->configManager->get('pim_icecat_connector.summary_description');
                if (!empty($pimAttributeCode)) {
                    $standardItem = $this->addProductValue(
                        $standardItem,
                        $locale,
                        $pimAttributeCode,
                        null,
                        (string)$icecatProduct->SummaryDescription->LongSummaryDescription,
                        null
                    );
                }

                $pimAttributeCode = $this->configManager->get('pim_icecat_connector.short_summary_description');
                if (!empty($pimAttributeCode)) {
                    $standardItem = $this->addProductValue(
                        $standardItem,
                        $locale,
                        $pimAttributeCode,
                        null,
                        (string)$icecatProduct->SummaryDescription->ShortSummaryDescription,
                        null
                    );
                }
            }

            foreach ($icecatProduct->ProductFeature as $xmlFeature) {
                $featureId = (int)$xmlFeature->Feature->attributes()['ID'];
                $pimCode = $this->attributeMapper->getMapped($featureId);
                if (!empty($pimCode)) {
                    $value = (string)$xmlFeature->attributes()['Value'];
                    $localValue = (string)$xmlFeature->LocalValue->attributes()['Value'];
                    $unit = (string)$xmlFeature->LocalValue->Measure->Signs->Sign;
                    $standardItem = $this->addProductValue(
                        $standardItem,
                        $locale,
                        $pimCode,
                        $value,
                        $localValue,
                        $unit
                    );
                }
            }

            $pictures = [];
            $icecatGallery = (array)$icecatProduct->ProductGallery;
            if (isset($icecatGallery['ProductPicture'])) {
                $pictures = $this->extractPictures($icecatGallery['ProductPicture']);
            }
            $pimAttributeCode = $this->configManager->get('pim_icecat_connector.pictures');
            if (!empty($pimAttributeCode)) {
                $standardItem = $this->addProductValue(
                    $standardItem,
                    $locale,
                    $pimAttributeCode,
                    null,
                    array_values($pictures),
                    null
                );
            }
        } catch (MapperException $e) {
            throw $e;
        } catch (\Exception $e) {
            if ($icecatProduct instanceof \SimpleXMLElement && (string)$icecatProduct['Code'] === '-1') {
                throw new XmlDecodeException($icecatProduct['ErrorMessage'], 0, $e);
            }
            throw new XmlDecodeException(sprintf('XML decode error for string %s', $xmlString), 0, $e);
        }

        return $standardItem;
    }

    /**
     * @param array  $standardItem
     * @param string $locale
     * @param string $pimCode
     * @param mixed  $value
     * @param mixed  $localValue
     * @param string $unit
     *
     * @return array
     */
    protected function addProductValue(array $standardItem, $locale, $pimCode, $value, $localValue, $unit)
    {
        $pimAttribute = $this->attributeRepository->findOneByIdentifier($pimCode);

        if (null === $pimAttribute
            || (!$pimAttribute->isLocalizable() && $locale != $this->fallbackLocale)) {
            return $standardItem;
        }

        if (!$pimAttribute->isLocalizable()) {
            $locale = null;
        }

        if (!$pimAttribute->isLocalizable()) {
            $locale = null;
        }

        $scope = null;
        if ($pimAttribute->isScopable()) {
            $scope = $this->scope;
        }

        if (AttributeTypes::METRIC === $pimAttribute->getType() && null !== $unit) {
            $localValue = $this->formatMetricValue($localValue, $unit);
        } elseif (AttributeTypes::PRICE_COLLECTION === $pimAttribute->getType() && null !== $unit) {
            $localValue = $this->formatPriceValue($localValue, $unit);
        } elseif (AttributeTypes::BOOLEAN === $pimAttribute->getType()) {
            $localValue = $value == 'Y' ? true : false;
        } elseif (AttributeTypes::NUMBER === $pimAttribute->getType()) {
            $localValue = $this->formatNumberValue($value);
        } else {
            $localValue = $this->findOptionCode($pimAttribute, $localValue);
        }

        $standardItem['values'][$pimCode] = [
            [
                'data'   => $localValue,
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
        $measure = $this->measureRepository->find($icecatUnit);

        return [
            'amount' => $icecatValue,
            'unit'   => $measure['unit'],
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
        return [
            [
                'data'     => $icecatValue,
                'currency' => $icecatUnit,
            ],
        ];
    }

    /**
     * @param string $icecatValue
     *
     * @return string|int
     */
    protected function formatNumberValue($icecatValue)
    {
        $intValue = filter_var($icecatValue, FILTER_VALIDATE_INT);

        return false !== $intValue ? $intValue : $icecatValue;
    }

    /**
     * @param AttributeInterface $pimAttribute
     * @param string             $icecatValue
     *
     * @return string
     */
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
     * @param SimpleXMLElement|SimpleXMLElement[] $icecatGallery
     *
     * @return string[]
     */
    protected function extractPictures($icecatGallery)
    {
        if ($icecatGallery instanceof SimpleXMLElement) {
            $icecatGallery = [$icecatGallery];
        }

        $pictures = [];
        foreach ($icecatGallery as $xmlPicture) {
            $url = (string)$xmlPicture->attributes()['Original'];
            if ('' !== trim($url)) {
                $pictures[] = $url;
            }
        }

        return $pictures;
    }

    /**
     * {@inheritdoc}
     */
    public function supportsDecoding($format)
    {
        return 'xml' === $format;
    }
}
