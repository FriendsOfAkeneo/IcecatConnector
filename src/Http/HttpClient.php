<?php

namespace Pim\Bundle\IcecatConnectorBundle\Http;

use GuzzleHttp\Client;
use GuzzleHttp\ClientInterface;
use Oro\Bundle\ConfigBundle\Config\ConfigManager;

/**
 * Http client used to fetch Icecat files
 *
 * @author    JM Leroux <jean-marie.leroux@akeneo.com>
 * @copyright 2016 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class HttpClient
{
    /**
     * Icecat API credentials = [ username, password ]
     *
     * @var string[]
     */
    private $credentials;

    /** @var ClientInterface */
    private $guzzle;

    /**
     * @param ConfigManager $configManager
     * @param string        $baseUri
     */
    public function __construct(ConfigManager $configManager, $baseUri)
    {
        $this->credentials = [
            $configManager->get('pim_icecat_connector.credentials_username'),
            $configManager->get('pim_icecat_connector.credentials_password'),
        ];
        $this->guzzle = new Client(['base_uri' => $baseUri]);
    }

    /**
     * @return ClientInterface
     */
    public function getGuzzle()
    {
        return $this->guzzle;
    }

    /**
     * @return \string[]
     */
    public function getCredentials()
    {
        return $this->credentials;
    }
}
