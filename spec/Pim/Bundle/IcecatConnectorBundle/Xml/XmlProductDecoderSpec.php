<?php

namespace spec\Pim\Bundle\IcecatConnectorBundle\Xml;

use Oro\Bundle\ConfigBundle\Config\ConfigManager;
use PhpSpec\ObjectBehavior;
use Pim\Bundle\CatalogBundle\Entity\AttributeOptionValue;
use Pim\Bundle\ExtendedAttributeTypeBundle\AttributeType\ExtendedAttributeTypes;
use Pim\Bundle\ExtendedMeasureBundle\Repository\MeasureRepositoryInterface;
use Pim\Bundle\IcecatConnectorBundle\Mapping\AttributeMapper;
use Pim\Bundle\IcecatConnectorBundle\Xml\XmlDecodeException;
use Pim\Component\Catalog\AttributeTypes;
use Pim\Component\Catalog\Model\AttributeInterface;
use Pim\Component\Catalog\Model\AttributeOptionInterface;
use Pim\Component\Catalog\Repository\AttributeRepositoryInterface;
use Prophecy\Argument;

class XmlProductDecoderSpec extends ObjectBehavior
{
    /** @var string */
    private $enXml;

    /** @var string */
    private $deXml;

    /** @var int */
    private $icecatBooleanFeatureId = 4963;

    /**
     * RAM capacity
     * @var int
     */
    private $icecatMetricFeatureId = 7861;

    /** @var int */
    private $icecatNumberIntegerFeatureId = 17983;

    /** @var int */
    private $icecatNumberFloatFeatureId = 27597;

    /** @var int */
    private $icecatTextFeatureId = 19653;

    /** @var int */
    private $icecatDropdownMultiFeatureId = 7061;

    function let(
        ConfigManager $configManager,
        AttributeMapper $attributeMapper,
        AttributeRepositoryInterface $attributeRepository,
        MeasureRepositoryInterface $extendedMeasureRepository
    ) {
        $xmlResourcesDirectory = __DIR__ . '/../Resources';
        $this->enXml = file_get_contents($xmlResourcesDirectory . '/8806088281285-en.xml');
        $this->deXml = file_get_contents($xmlResourcesDirectory . '/8806088281285-de.xml');

        $this->beConstructedWith(
            $configManager,
            $attributeMapper,
            $attributeRepository,
            $extendedMeasureRepository,
            'ecommerce'
        );
    }

    function it_throws_exception_on_invalid_xml()
    {
        $this->shouldThrow(XmlDecodeException::class)
            ->duringDecode('foo', null, ['locale' => 'en_US', 'fallback_locale' => 'en_US']);
    }

    function it_can_decode_a_en_string_with_default_icecat_attributes(
        AttributeInterface $descriptionAttribute,
        AttributeInterface $shortDescriptionAttribute,
        AttributeInterface $summaryDescriptionAttribute,
        AttributeInterface $shortSummaryDescriptionAttribute,
        AttributeInterface $picturesAttribute,
        $configManager,
        $attributeRepository
    ) {
        $descriptionAttribute->isLocalizable()->willReturn(false);
        $descriptionAttribute->isScopable()->willReturn(false);
        $descriptionAttribute->getType()->willReturn(AttributeTypes::TEXT);
        $descriptionAttribute->getOptions()->willReturn([]);

        $shortDescriptionAttribute->isLocalizable()->willReturn(false);
        $shortDescriptionAttribute->isScopable()->willReturn(false);
        $shortDescriptionAttribute->getType()->willReturn(AttributeTypes::TEXT);
        $shortDescriptionAttribute->getOptions()->willReturn([]);

        $summaryDescriptionAttribute->isLocalizable()->willReturn(false);
        $summaryDescriptionAttribute->isScopable()->willReturn(false);
        $summaryDescriptionAttribute->getType()->willReturn(AttributeTypes::TEXT);
        $summaryDescriptionAttribute->getOptions()->willReturn([]);

        $shortSummaryDescriptionAttribute->isLocalizable()->willReturn(false);
        $shortSummaryDescriptionAttribute->isScopable()->willReturn(false);
        $shortSummaryDescriptionAttribute->getType()->willReturn(AttributeTypes::TEXT);
        $shortSummaryDescriptionAttribute->getOptions()->willReturn([]);

        $picturesAttribute->isLocalizable()->willReturn(false);
        $picturesAttribute->isScopable()->willReturn(false);
        $picturesAttribute->getType()->willReturn(ExtendedAttributeTypes::TEXT_COLLECTION);
        $picturesAttribute->getOptions()->willReturn([]);

        $configManager->get('pim_icecat_connector.description')->willReturn('description');
        $configManager->get('pim_icecat_connector.short_description')->willReturn('short_description');
        $configManager->get('pim_icecat_connector.summary_description')->willReturn('summary_description');
        $configManager->get('pim_icecat_connector.short_summary_description')->willReturn('short_summary_description');
        $configManager->get('pim_icecat_connector.pictures')->willReturn('pictures');

        $attributeRepository->findOneByIdentifier('description')->willReturn($descriptionAttribute);
        $attributeRepository->findOneByIdentifier('short_description')->willReturn($shortDescriptionAttribute);
        $attributeRepository->findOneByIdentifier('summary_description')->willReturn($summaryDescriptionAttribute);
        $attributeRepository->findOneByIdentifier('short_summary_description')->willReturn($shortSummaryDescriptionAttribute);
        $attributeRepository->findOneByIdentifier('pictures')->willReturn($picturesAttribute);

        $standardItem = [
            'values' =>
                [
                    'description' =>
                        [
                            [
                                'data' => 'This is the en_US long description.',
                                'locale' => NULL,
                                'scope' => NULL,
                            ],
                        ],
                    'short_description' =>
                        [
                            [
                                'data' => 'en_US short description',
                                'locale' => NULL,
                                'scope' => NULL,
                            ],
                        ],
                    'summary_description' =>
                        [
                            [
                                'data' => 'This is the en_US long summary description',
                                'locale' => NULL,
                                'scope' => NULL,
                            ],
                        ],
                    'short_summary_description' =>
                        [
                            [
                                'data' => 'en_US short summary description',
                                'locale' => NULL,
                                'scope' => NULL,
                            ],
                        ],
                    'pictures' =>
                        [
                            0 =>
                                [
                                    'data' => [
                                        "http://images.icecat.biz/img/gallery_raw/pic1.jpeg",
                                        "http://images.icecat.biz/img/gallery_raw/pic2.jpeg",
                                        "http://images.icecat.biz/img/gallery_raw/pic3.jpeg",
                                        "http://images.icecat.biz/img/gallery_raw/pic4.jpeg"
                                    ],
                                    'locale' => NULL,
                                    'scope' => NULL,
                                ],
                        ],
                ],
        ];

        $this->decode($this->enXml, null, ['locale' => 'en_US', 'fallback_locale' => 'en_US'])
            ->shouldReturn($standardItem);
    }

    function it_can_decode_a_en_string_with_boolean_attributes(
        AttributeInterface $booleanAttribute,
        $attributeMapper,
        $attributeRepository
    ) {
        $attributeMapper->getMapped($this->icecatBooleanFeatureId)->willReturn('pim_boolean');
        $attributeMapper->getMapped(Argument::any())->willReturn(null);

        $booleanAttribute->isLocalizable()->willReturn(false);
        $booleanAttribute->isScopable()->willReturn(false);
        $booleanAttribute->getType()->willReturn(AttributeTypes::BOOLEAN);
        $booleanAttribute->getOptions()->willReturn([]);

        $attributeRepository->findOneByIdentifier('pim_boolean')->willReturn($booleanAttribute);

        $standardItem = [
            'values' =>
                [
                    'pim_boolean' =>
                        [
                            [
                                'data' => true,
                                'locale' => NULL,
                                'scope' => NULL,
                            ],
                        ],
                ],
        ];

        $this->decode($this->enXml, null, ['locale' => 'en_US', 'fallback_locale' => 'en_US'])->shouldReturn($standardItem);
    }

    function it_can_decode_a_en_string_with_metric_attributes(
        AttributeInterface $metricAttribute,
        $attributeMapper,
        $attributeRepository,
        $extendedMeasureRepository
    ) {
        $attributeMapper->getMapped($this->icecatMetricFeatureId)->willReturn('pim_metric');
        $attributeMapper->getMapped(Argument::any())->willReturn(null);

        $metricAttribute->isLocalizable()->willReturn(false);
        $metricAttribute->isScopable()->willReturn(false);
        $metricAttribute->getType()->willReturn(AttributeTypes::METRIC);
        $metricAttribute->getOptions()->willReturn([]);

        $extendedMeasureRepository->find('GB')->willReturn(['unit' => 'Gb']);

        $attributeRepository->findOneByIdentifier('pim_metric')->willReturn($metricAttribute);

        $standardItem = [
            'values' =>
                [
                    'pim_metric' =>
                        [
                            [
                                'data' => ['amount' => '4', 'unit' => 'Gb'],
                                'locale' => NULL,
                                'scope' => NULL,
                            ],
                        ],
                ],
        ];

        $this->decode($this->enXml, null, ['locale' => 'en_US', 'fallback_locale' => 'en_US'])->shouldReturn($standardItem);
    }

    function it_can_decode_a_en_string_with_number_integer_attributes(
        AttributeInterface $numberAttribute,
        $attributeMapper,
        $attributeRepository
    ) {
        $attributeMapper->getMapped($this->icecatNumberIntegerFeatureId)->willReturn('pim_number');
        $attributeMapper->getMapped(Argument::any())->willReturn(null);

        $numberAttribute->isLocalizable()->willReturn(false);
        $numberAttribute->isScopable()->willReturn(false);
        $numberAttribute->getType()->willReturn(AttributeTypes::NUMBER);
        $numberAttribute->getOptions()->willReturn([]);

        $attributeRepository->findOneByIdentifier('pim_number')->willReturn($numberAttribute);

        $standardItem = [
            'values' =>
                [
                    'pim_number' =>
                        [
                            [
                                'data' => 4,
                                'locale' => NULL,
                                'scope' => NULL,
                            ],
                        ],
                ],
        ];

        $this->decode($this->enXml, null, ['locale' => 'en_US', 'fallback_locale' => 'en_US'])->shouldReturn($standardItem);
    }

    function it_can_decode_a_en_string_with_number_float_attributes(
        AttributeInterface $numberAttribute,
        $attributeMapper,
        $attributeRepository
    ) {
        $attributeMapper->getMapped($this->icecatNumberFloatFeatureId)->willReturn('pim_number');
        $attributeMapper->getMapped(Argument::any())->willReturn(null);

        $numberAttribute->isLocalizable()->willReturn(false);
        $numberAttribute->isScopable()->willReturn(false);
        $numberAttribute->getType()->willReturn(AttributeTypes::NUMBER);
        $numberAttribute->getOptions()->willReturn([]);

        $attributeRepository->findOneByIdentifier('pim_number')->willReturn($numberAttribute);

        $standardItem = [
            'values' =>
                [
                    'pim_number' =>
                        [
                            [
                                'data' => '1.7',
                                'locale' => NULL,
                                'scope' => NULL,
                            ],
                        ],
                ],
        ];

        $this->decode($this->enXml, null, ['locale' => 'en_US', 'fallback_locale' => 'en_US'])->shouldReturn($standardItem);
    }

    function it_can_decode_a_en_string_with_text_attributes(
        AttributeInterface $textAttribute,
        $attributeMapper,
        $attributeRepository
    ) {
        $attributeMapper->getMapped($this->icecatTextFeatureId)->willReturn('pim_text');
        $attributeMapper->getMapped(Argument::any())->willReturn(null);

        $textAttribute->isLocalizable()->willReturn(false);
        $textAttribute->isScopable()->willReturn(false);
        $textAttribute->getType()->willReturn(AttributeTypes::TEXT);
        $textAttribute->getOptions()->willReturn([]);

        $attributeRepository->findOneByIdentifier('pim_text')->willReturn($textAttribute);

        $standardItem = [
            'values' =>
                [
                    'pim_text' =>
                        [
                            [
                                'data' => 'Single SIM',
                                'locale' => null,
                                'scope' => null,
                            ],
                        ],
                ],
        ];

        $this->decode($this->enXml, null, ['locale' => 'en_US', 'fallback_locale' => 'en_US'])->shouldReturn($standardItem);
    }

    function it_can_decode_a_en_string_with_multiselect_attributes(
        AttributeInterface $multiSelectAttribute,
        AttributeOptionInterface $option1,
        AttributeOptionValue $optionValue1,
        AttributeOptionInterface $option2,
        AttributeOptionValue $optionValue2,
        $attributeMapper,
        $attributeRepository
    ) {
        $attributeMapper->getMapped($this->icecatDropdownMultiFeatureId)->willReturn('pim_multiselect');
        $attributeMapper->getMapped(Argument::any())->willReturn(null);

        $option1->setLocale(null)->willReturn(null);
        $option2->setLocale(null)->willReturn(null);
        $option1->getOptionValue()->willReturn($optionValue1);
        $option2->getOptionValue()->willReturn($optionValue2);

        $multiSelectAttribute->isLocalizable()->willReturn(false);
        $multiSelectAttribute->isScopable()->willReturn(false);
        $multiSelectAttribute->getType()->willReturn(AttributeTypes::OPTION_MULTI_SELECT);
        $multiSelectAttribute->getOptions()->willReturn([$option1, $option2]);

        $attributeRepository->findOneByIdentifier('pim_multiselect')->willReturn($multiSelectAttribute);

        $standardItem = [
            'values' =>
                [
                    'pim_multiselect' =>
                        [
                            [
                                'data' => ['850','900','1800','1900'],
                                'locale' => null,
                                'scope' => null,
                            ],
                        ],
                ],
        ];

        $this->decode($this->enXml, null, ['locale' => 'en_US', 'fallback_locale' => 'en_US'])->shouldReturn($standardItem);
    }

    function it_can_only_decode_xml_format()
    {
        $this->supportsDecoding('xml')->shouldReturn(true);
        $this->supportsDecoding('foo')->shouldReturn(false);
    }
}
