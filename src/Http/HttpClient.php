<?php

namespace Pim\Bundle\IcecatConnectorBundle\Http;

use GuzzleHttp\Client;
use GuzzleHttp\ClientInterface;

/**
 * Http client used to fetch Icecat files
 *
 * @author    JM Leroux <jean-marie.leroux@akeneo.com>
 * @copyright 2016 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class HttpClient
{
    /** @var string[] */
    private $credentials;

    /** @var ClientInterface */
    private $guzzle;

    /**
     * @param string $username
     * @param string $password
     * @param string $baseUri
     */
    public function __construct($username, $password, $baseUri)
    {
        $this->credentials = [$username, $password];
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
