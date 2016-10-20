<?php

namespace Pim\Bundle\IcecatConnectorBundle\Parser;

use Pim\Bundle\IcecatConnectorBundle\Model\Feature;

/**
 * @author    JM Leroux <jean-marie.leroux@akeneo.com>
 * @copyright 2016 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class FeatureToAttributeParser
{
    const XPATH_DESCRIPTION = 'Descriptions/Description[@langid=1]';
    const XPATH_NAME = 'Names/Name[@langid=1]';
    const XPATH_SIGN = 'Measure/Signs/Sign[@langid=1]';

    /**
     * @param \SimpleXMLElement $element
     *
     * @return Feature
     */
    public function parseNode(\SimpleXMLElement $element)
    {
        $feature = new Feature();

        $attributes = $element->attributes();
        $feature->setId((int) $attributes['ID']);
        $feature->setType((string) $attributes['Type']);
        if ($element->xpath(self::XPATH_NAME)) {
            $feature->setName((string) $element->xpath(self::XPATH_NAME)[0]);
        }
        if ($element->xpath(self::XPATH_DESCRIPTION)) {
            $feature->setDescription((string) $element->xpath(self::XPATH_DESCRIPTION)[0]);
        }

        $measureId = null;
        $measure = $element->Measure;
        if ($measure) {
            $feature->setMeasureId((int) $measure->attributes()['ID']);
        }

        $values = $element->RestrictedValues;
        if ($values) {
            foreach ($values->children() as $value) {
                if (trim($value)) {
                    $feature->addRestrictedValue(trim($value));
                }
            }
        }

        return $feature;
    }

    /**
     * Feature object to array normalizer
     *
     * @param Feature $feature
     *
     * @return array
     */
    public function normalize(Feature $feature)
    {
        $defaults = [
            'code'                   => 'a_string',
            'type'                   => 'pim_catalog_text',
            'labels'                 => [
                'en_US' => 'The label',
            ],
            'group'                  => 'other',
            'unique'                 => false,
            'useable_as_grid_filter' => false,
            'allowed_extensions'     => null,
            'metric_family'          => null,
            'default_metric_unit'    => null,
            'reference_data_name'    => null,
            'available_locales'      => [
                0 => 'en_US',
            ],
            'max_characters'         => null,
            'validation_rule'        => null,
            'validation_regexp'      => null,
            'wysiwyg_enabled'        => false,
            'number_min'             => null,
            'number_max'             => null,
            'decimals_allowed'       => false,
            'negative_allowed'       => false,
            'date_min'               => null,
            'date_max'               => null,
            'max_file_size'          => null,
            'minimum_input_length'   => 0,
            'sort_order'             => 0,
            'localizable'            => true,
            'scopable'               => false,
        ];

        $convertedItem = [
            'code'   => $this->slugifyCode($feature->getName()),
            'type'   => $feature->getType(),
            'labels' => [
                'en_US' => $feature->getName(),
            ],
            'group'                  => 'other',
            'metric_family'          => null,
            'default_metric_unit'    => null,
            'number_min'             => null,
            'number_max'             => null,
        ];

        return array_merge($defaults, $convertedItem);
    }

    protected function slugifyCode($code)
    {
        $clean = strtolower($code);
        $clean = preg_replace('/[^a-z0-9]/', '_', $clean);
        return $clean;
    }
}
