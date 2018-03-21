<?php

namespace Pim\Bundle\IcecatConnectorBundle\Enrich;

class LocaleResolver implements LocaleResolverInterface
{
    /** @var array */
    private $localeMapping;

    /**
     * @param array $localeMapping
     */
    public function __construct($localeMapping)
    {
        $this->localeMapping = $localeMapping;
    }

    /**
     * @param $icecatLocaleCode
     *
     * @return string
     */
    public function getPimLocaleCode($icecatLocaleCode)
    {
        return $this->localeMapping[$icecatLocaleCode]['locale'];
    }
}
