<?php

namespace Pim\Bundle\IcecatConnectorBundle\Tests\Controller;

use Pim\Bundle\IcecatConnectorBundle\Controller\ConnectionController;
use Pim\Bundle\IcecatConnectorBundle\Tests\AbstractTestCase;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * @author    Mathias METAYER <mathias.metayer@akeneo.com>
 * @copyright ${YEAR} Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class ConnectionControllerTest extends AbstractTestCase
{
    /** @var array */
    private static $credentials = [];

    protected function setUp()
    {
        parent::setUp();
        $config = $this->get('oro_config.global');
        self::$credentials = [
            'username' => $config->get('pim_icecat_connector.credentials_username'),
            'password' => $config->get('pim_icecat_connector.credentials_password'),
        ];
    }

    public function testIcecatConnectionOk()
    {
        /** @var ConnectionController $controller */
        $controller = $this->get('pim_icecat_connector.controller.connection');
        $request = $this->getRequestMock(static::$credentials['username'], static::$credentials['password']);
        $response = $controller->checkCredentials($request);

        $this->assertEquals(Response::HTTP_OK, $response->getStatusCode());
    }

    public function testIcecatConnectionKo()
    {
        /** @var ConnectionController $controller */
        $controller = $this->get('pim_icecat_connector.controller.connection');

        $invalidData = [
            [null, null], // empty credentials
            ['foo', 'bar'], // invalid login
            [static::$credentials['username'], 'invalidpassword'], // invalid password
        ];

        foreach ($invalidData as $invalidCredentials) {
            list($username, $password) = $invalidCredentials;
            $request = $this->getRequestMock($username, $password);
            $response = $controller->checkCredentials($request);
            $this->assertEquals(Response::HTTP_UNAUTHORIZED, $response->getStatusCode());
        }
    }

    /**
     * @param $username
     * @param $password
     * @return \PHPUnit_Framework_MockObject_MockObject
     */
    private function getRequestMock($username, $password)
    {
        $map = [
            ['username', null, $username],
            ['password', null, $password],
        ];

        $requestMock = $this->getMockBuilder(Request::class)
            ->disableOriginalConstructor()
            ->getMock();
        $requestMock->method('get')
            ->will($this->returnValueMap($map));

        return $requestMock;
    }
}
