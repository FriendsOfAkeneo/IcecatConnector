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
    public function testIcecatConnectionOk()
    {
        $credentials = $this->getCredentials();
        /** @var ConnectionController $controller */
        $controller = $this->get('pim_icecat_connector.controller.connection');
        $request = $this->getRequestMock($credentials['username'], $credentials['password']);
        $response = $controller->checkCredentials($request);

        $this->assertEquals(Response::HTTP_OK, $response->getStatusCode());
    }

    public function testIcecatConnectionKo()
    {
        /** @var ConnectionController $controller */
        $controller = $this->get('pim_icecat_connector.controller.connection');
        $request = $this->getRequestMock('foo', 'bar');
        $response = $controller->checkCredentials($request);

        $this->assertEquals(Response::HTTP_NOT_FOUND, $response->getStatusCode());

        $request = $this->getRequestMock('akeneo-test', 'bar');
        $response = $controller->checkCredentials($request);

        $this->assertEquals(Response::HTTP_UNAUTHORIZED, $response->getStatusCode());

        $request = $this->getRequestMock($this->getCredentials()['username'], 'invalidpassword');
        $response = $controller->checkCredentials($request);

        $this->assertEquals(Response::HTTP_UNAUTHORIZED, $response->getStatusCode());
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
