<?php

namespace Pim\Bundle\IcecatConnectorBundle\Enrich;

interface LocaleResolverInterface
{
    /**
     * @param $icecatLocaleCode
     *
     * @return string
     */
    public function getPimLocaleCode($icecatLocaleCode);
}
