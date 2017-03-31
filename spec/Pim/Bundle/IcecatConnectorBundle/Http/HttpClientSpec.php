<?php

namespace spec\Pim\Bundle\IcecatConnectorBundle\Http;

use GuzzleHttp\ClientInterface;
use Oro\Bundle\ConfigBundle\Config\ConfigManager;
use PhpSpec\ObjectBehavior;

class HttpClientSpec extends ObjectBehavior
{
    private $credentials = ['fooName', 'fooPwd'];

    function let(ConfigManager $configManager)
    {
        $configManager->get('pim_icecat_connector.credentials_username')
            ->willReturn('fooName');
        $configManager->get('pim_icecat_connector.credentials_password')
            ->willReturn('fooPwd');
        $baseUri = 'http://baseUri.dev.null';
        $this->beConstructedWith($configManager, $baseUri);
    }

    function it_can_return_client()
    {
        $this->getGuzzle()->shouldImplement(ClientInterface::class);
    }

    function it_can_return_credentials()
    {
        $this->getCredentials()->shouldReturn($this->credentials);
    }
}
