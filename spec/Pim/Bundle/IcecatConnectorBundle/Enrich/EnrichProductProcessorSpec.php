<?php

namespace spec\Pim\Bundle\IcecatConnectorBundle\Enrich;

use Akeneo\Component\Batch\Model\StepExecution;
use Oro\Bundle\ConfigBundle\Config\ConfigManager;
use PhpSpec\ObjectBehavior;
use Pim\Bundle\IcecatConnectorBundle\Enrich\EnrichProductProcessor;
use Pim\Bundle\IcecatConnectorBundle\Enrich\LocaleResolverInterface;
use Pim\Bundle\IcecatConnectorBundle\Http\HttpClient;
use Pim\Bundle\IcecatConnectorBundle\Xml\XmlProductDecoder;
use Pim\Component\Catalog\Updater\ProductUpdater;

class EnrichProductProcessorSpec extends ObjectBehavior
{
    function let(
        HttpClient $httpClient,
        XmlProductDecoder $xmlProductDecoder,
        ProductUpdater $productUpdater,
        ConfigManager $config,
        LocaleResolverInterface $localeResolver,
        StepExecution $stepExecution
    ) {
        $config->get('pim_icecat_connector.ean_attribute')->willReturn('ean_attribute');
        $config->get('pim_icecat_connector.locales')->willReturn('US,FR');
        $config->get('pim_icecat_connector.fallback_locale')->willReturn('US');
        $this->beConstructedWith($httpClient, $xmlProductDecoder, $productUpdater, $config, $localeResolver, '/icecatendpoint/%s');
        $this->setStepExecution($stepExecution);
    }

    function it_is_initializable()
    {
        $this->shouldHaveType(EnrichProductProcessor::class);
    }
}
