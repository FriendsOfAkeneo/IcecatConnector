<?php

namespace spec\Pim\Bundle\IcecatConnectorBundle\Controller;

use Oro\Bundle\ConfigBundle\Config\ConfigManager;
use PhpSpec\ObjectBehavior;
use Pim\Bundle\IcecatConnectorBundle\Http\HttpClient;

class ConnectionControllerSpec extends ObjectBehavior
{
    function it_throws_exception_on_invalid_construct_parameters(
        HttpClient $client,
        ConfigManager $config
    )
    {
        $icecatProductEndPoint = 'icecatProductEndPoint';

        // provide valid arguments to make sure subject can be initialized
        $this->beConstructedWith([$client, $config, $icecatProductEndPoint]);

        $this->shouldThrow(\Exception::class)->during('__construct', [null, $config, $icecatProductEndPoint]);
        $this->shouldThrow(\Exception::class)->during('__construct', [$client, null, $icecatProductEndPoint]);
    }
}
