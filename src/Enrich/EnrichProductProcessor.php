<?php

namespace Pim\Bundle\IcecatConnectorBundle\Enrich;

use Akeneo\Component\Batch\Item\DataInvalidItem;
use Oro\Bundle\ConfigBundle\Config\ConfigManager;
use Pim\Bundle\EnrichBundle\Connector\Processor\AbstractProcessor;
use Pim\Bundle\IcecatConnectorBundle\Http\HttpClient;
use Pim\Bundle\IcecatConnectorBundle\Xml\XmlProductDecoder;
use Pim\Component\Catalog\Exception\InvalidArgumentException;
use Pim\Component\Catalog\Updater\ProductUpdater;

/**
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

    /** @var ProductUpdater */
    private $productUpdater;

    /** @var string */
    protected $icecatProductEndpoint;

    /** @var string */
    protected $eanAttributeCode;

    /**
     * EnrichProductProcessor constructor.
     *
     * @param HttpClient        $httpClient
     * @param XmlProductDecoder $xmlProductDecoder
     * @param ProductUpdater    $productUpdater
     * @param ConfigManager     $config
     * @param string            $icecatProductEndpoint
     */
    public function __construct(
        HttpClient $httpClient,
        XmlProductDecoder $xmlProductDecoder,
        ProductUpdater $productUpdater,
        ConfigManager $config,
        $icecatProductEndpoint
    )
    {
        $this->httpClient = $httpClient;
        $this->xmlProductDecoder = $xmlProductDecoder;
        $this->icecatProductEndpoint = $icecatProductEndpoint;
        $this->productUpdater = $productUpdater;

        $this->eanAttributeCode = $config->get('pim_icecat_connector.ean_attribute');
    }

    /**
     * {@inheritdoc}
     */
    public function process($item)
    {
        $eanValue = (int) $item->getValue($this->eanAttributeCode)->getData();

        $query = sprintf($this->icecatProductEndpoint, $eanValue);

        $guzzle = $this->httpClient->getGuzzle();
        $res = $guzzle->request('GET', $query, [
            'auth' => $this->httpClient->getCredentials(),
        ]);
        $standardProduct = $this->xmlProductDecoder->decode($res->getBody()->getContents(), 'xml');
        try {
            $this->productUpdater->update($item, $standardProduct);
        } catch (InvalidArgumentException $e) {
            $this->stepExecution->addWarning($e->getMessage(), [], new DataInvalidItem($item));

            return null;
        }

        return $item;
    }
}
