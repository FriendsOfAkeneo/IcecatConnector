<?php

namespace Pim\Bundle\IcecatConnectorBundle\Http;

use GuzzleHttp\Client;
use GuzzleHttp\ClientInterface;

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
