<?php

namespace Pim\Bundle\IcecatConnectorBundle\Controller;

use Oro\Bundle\ConfigBundle\Config\ConfigManager;
use Pim\Bundle\IcecatConnectorBundle\Http\HttpClient;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * @author    Remy Betus <remy.betus@akeneo.com>
 * @copyright 2017 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class ConnectionController
{
    /** @const */
    const INVALID_PASSWORD = 'Login or password are invalid';

    /** @const */
    const INVALID_LOGIN = 'The requested XML data-sheet is not present in the Icecat database.';

    /** @var HttpClient */
    protected $client;

    /** @var ConfigManager */
    protected $config;

    /** @var string */
    protected $icecatProductEndpoint;

    /**
     * @param HttpClient    $client
     * @param ConfigManager $config
     * @param string        $icecatProductEndpoint
     */
    public function __construct(
        HttpClient $client,
        ConfigManager $config,
        $icecatProductEndpoint
    ) {
        $this->client = $client;
        $this->config = $config;
        $this->icecatProductEndpoint = $icecatProductEndpoint;
    }

    /**
     * Checks if the credentials to reach IceCat API are valid or not.
     *
     * @param Request $request
     *
     * @return Response
     */
    public function checkCredentials(Request $request)
    {
        // This is a real product EAN only to build a query
        $eanValue = '4948382487834';

        $username = $request->get('username');
        $password = $request->get('password');

        $query = sprintf($this->icecatProductEndpoint, $eanValue, 'US');

        $guzzle = $this->client->getGuzzle();
        $guzzleResponse = $guzzle->request(
            'GET',
            '',
            [
                'auth' => [$username, $password],
                'query' => $query,
            ]
        );

        $content = $guzzleResponse->getBody()->getContents();

        $statusCode = Response::HTTP_UNAUTHORIZED;
        if (false === strpos($content, self::INVALID_LOGIN)
            && false === strpos($content, self::INVALID_PASSWORD)
        ) {
            $statusCode = Response::HTTP_OK;
        }

        return new Response($content, $statusCode);
    }
}
