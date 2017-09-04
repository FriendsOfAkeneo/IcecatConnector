<?php

namespace spec\Pim\Bundle\IcecatConnectorBundle\Enrich;

use Akeneo\Component\Batch\Model\StepExecution;
use Oro\Bundle\ConfigBundle\Config\ConfigManager;
use PhpSpec\ObjectBehavior;
use Pim\Bundle\IcecatConnectorBundle\Enrich\EnrichProductProcessor;
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
        StepExecution $stepExecution
    ) {
        $config->get('pim_icecat_connector.ean_attribute')->willReturn('ean_attribute');
        $this->beConstructedWith($httpClient, $xmlProductDecoder, $productUpdater, $config, '/icecatendpoint/%s');
        $this->setStepExecution($stepExecution);
    }

    function it_is_initializable()
    {
        $this->shouldHaveType(EnrichProductProcessor::class);
    }
}
