<?php

namespace Pim\Bundle\IcecatConnectorBundle\Reader;

use Oro\Bundle\ConfigBundle\Config\ConfigManager;
use Pim\Component\Catalog\Converter\MetricConverter;
use Pim\Component\Catalog\Manager\CompletenessManager;
use Pim\Component\Catalog\Query\ProductQueryBuilderFactoryInterface;
use Pim\Component\Catalog\Repository\ChannelRepositoryInterface;
use Pim\Component\Connector\Reader\Database\ProductReader as BaseReader;

/**
 * @author    JM Leroux <jean-marie.leroux@akeneo.com>
 * @copyright 2016 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class ProductReader extends BaseReader
{
    /** @var string */
    protected $eanAttributeCode;

    /**
     * @param ProductQueryBuilderFactoryInterface $pqbFactory
     * @param ChannelRepositoryInterface          $channelRepository
     * @param CompletenessManager                 $completenessManager
     * @param MetricConverter                     $metricConverter
     * @param ConfigManager                       $configManager
     * @param bool                                $generateCompleteness
     */
    public function __construct(
        ProductQueryBuilderFactoryInterface $pqbFactory,
        ChannelRepositoryInterface $channelRepository,
        CompletenessManager $completenessManager,
        MetricConverter $metricConverter,
        ConfigManager $configManager,
        $generateCompleteness
    ) {
        parent::__construct($pqbFactory, $channelRepository, $completenessManager, $metricConverter, $generateCompleteness);
        $this->eanAttributeCode = $configManager->get('pim_icecat_connector.ean_attribute');
    }

    /**
     * Returns the filters from the configuration.
     * The parameters can be in the 'filters' root node, or in filters data node (e.g. for export).
     *
     * @return array
     */
    protected function getConfiguredFilters()
    {
        $filters = $this->stepExecution->getJobParameters()->get('filters');

        if (array_key_exists('data', $filters)) {
            $filters = $filters['data'];
        }

        $filters[] = [
            'field'    => $this->eanAttributeCode,
            'operator' => 'NOT EMPTY',
            'value'    => null,
        ];

        return array_filter($filters, function ($filter) {
            return count($filter) > 0;
        });
    }
}
