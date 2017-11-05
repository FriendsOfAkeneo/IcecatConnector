<?php

namespace spec\Pim\Bundle\IcecatConnectorBundle\Enrich;

use PhpSpec\ObjectBehavior;
use Pim\Bundle\IcecatConnectorBundle\Enrich\LocaleResolver;
use Pim\Bundle\IcecatConnectorBundle\Enrich\LocaleResolverInterface;

class LocaleResolverSpec extends ObjectBehavior
{
    private $locateMapping = [
        'US' => [
            'locale' => 'en_US',
            'label' => 'English US',
        ],
        'FR' => [
            'locale' => 'fr_FR',
            'label' => 'French',
        ],
    ];

    function let()
    {
        $this->beConstructedWith($this->locateMapping);
    }

    function it_is_initializable()
    {
        $this->shouldHaveType(LocaleResolver::class);
        $this->shouldImplement(LocaleResolverInterface::class);
    }

    function it_gets_pim_locale()
    {
        $this->getPimLocaleCode('US')->shouldReturn('en_US');
        $this->getPimLocaleCode('FR')->shouldReturn('fr_FR');
    }
}
