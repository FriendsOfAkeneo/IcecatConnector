<?php

namespace spec\Pim\Bundle\IcecatConnectorBundle\Reader;

use Oro\Bundle\ConfigBundle\Config\ConfigManager;
use PhpSpec\ObjectBehavior;
use Pim\Bundle\IcecatConnectorBundle\Reader\ProductReader;
use Pim\Component\Catalog\Converter\MetricConverter;
use Pim\Component\Catalog\Manager\CompletenessManager;
use Pim\Component\Catalog\Query\ProductQueryBuilderFactoryInterface;
use Pim\Component\Catalog\Repository\ChannelRepositoryInterface;

class ProductReaderSpec extends ObjectBehavior
{
    function let(
        ProductQueryBuilderFactoryInterface $pqbFactory,
        ChannelRepositoryInterface $channelRepository,
        CompletenessManager $completenessManager,
        MetricConverter $metricConverter,
        ConfigManager $configManager
    ) {
        $configManager->get('pim_icecat_connector.ean_attribute')->willReturn(null);
        $this->beConstructedWith($pqbFactory, $channelRepository, $completenessManager, $metricConverter, $configManager, true);
    }

    function it_is_initializable()
    {
        $this->shouldHaveType(ProductReader::class);
    }
}
