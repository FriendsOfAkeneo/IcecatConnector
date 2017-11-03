<?php

namespace Pim\Bundle\IcecatConnectorBundle\Enrich;

use Akeneo\Component\Batch\Item\DataInvalidItem;
use Oro\Bundle\ConfigBundle\Config\ConfigManager;
use Pim\Bundle\EnrichBundle\Connector\Processor\AbstractProcessor;
use Pim\Bundle\IcecatConnectorBundle\Exception\MapperException;
use Pim\Bundle\IcecatConnectorBundle\Http\HttpClient;
use Pim\Bundle\IcecatConnectorBundle\Xml\XmlProductDecoder;
use Pim\Component\Catalog\Exception\InvalidArgumentException;
use Pim\Component\Catalog\Updater\ProductUpdater;

/**
 * Processor to enrich product information from Icecat.
 * It calls Icecat API on every configured locales and enrich attribute data
 *
 * @author    JM Leroux <jean-marie.leroux@akeneo.com>
 * @copyright 2016 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class EnrichProductProcessor extends AbstractProcessor
{
    /** @var HttpClient */
    protected $httpClient;

    /** @var XmlProductDecoder */
    protected $xmlProductDecoder;

    /** @var string */
    protected $fallbackLocale;

    /** @var ProductUpdater */
    private $productUpdater;

    /** @var string */
    protected $icecatProductEndpoint;

    /** @var string */
    protected $eanAttributeCode;

    /** @var string[] */
    protected $icecatLocales;

    /** @var LocaleResolverInterface */
    protected $localeResolver;

    /**
     * EnrichProductProcessor constructor.
     *
     * @param HttpClient              $httpClient
     * @param XmlProductDecoder       $xmlProductDecoder
     * @param ProductUpdater          $productUpdater
     * @param ConfigManager           $config
     * @param LocaleResolverInterface $localeResolver
     * @param string                  $icecatProductEndpoint
     */
    public function __construct(
        HttpClient $httpClient,
        XmlProductDecoder $xmlProductDecoder,
        ProductUpdater $productUpdater,
        ConfigManager $config,
        LocaleResolverInterface $localeResolver,
        $icecatProductEndpoint
    ) {
        $this->httpClient = $httpClient;
        $this->xmlProductDecoder = $xmlProductDecoder;
        $this->icecatProductEndpoint = $icecatProductEndpoint;
        $this->productUpdater = $productUpdater;

        $this->eanAttributeCode = $config->get('pim_icecat_connector.ean_attribute');
        $this->icecatLocales = explode(',', $config->get('pim_icecat_connector.locales'));
        $this->fallbackLocale = $config->get('pim_icecat_connector.fallback_locale');
        $this->localeResolver = $localeResolver;
    }

    /**
     * {@inheritdoc}
     */
    public function process($item)
    {
        $eanValue = (string)$item->getValue($this->eanAttributeCode)->getData();

        foreach ($this->icecatLocales as $icecatLocale) {
            $query = sprintf($this->icecatProductEndpoint, $eanValue, $icecatLocale);

            $guzzle = $this->httpClient->getGuzzle();
            $res = $guzzle->request('GET', '', [
                'auth' => $this->httpClient->getCredentials(),
                'query' => $query,
            ]);
            $context = [
                'locale' => $this->localeResolver->getPimLocaleCode($icecatLocale),
                'fallback_locale' => $this->localeResolver->getPimLocaleCode($this->fallbackLocale),
            ];
            try {
                $standardProduct = $this->xmlProductDecoder->decode($res->getBody()->getContents(), 'xml', $context);
                $this->productUpdater->update($item, $standardProduct);
            } catch (InvalidArgumentException $e) {
                $this->stepExecution->addWarning($e->getMessage(), [], new DataInvalidItem($item));

                return null;
            } catch (MapperException $e) {
                $this->stepExecution->addFailureException($e);

                return null;
            } catch (\Exception $e) {
                $this->stepExecution->addWarning($e->getMessage(), [], new DataInvalidItem($item));

                return null;
            }
        }

        return $item;
    }
}
