<?php

namespace Pim\Bundle\IcecatConnectorBundle\Tests;

use Akeneo\Test\Integration\Configuration;
use Akeneo\Test\IntegrationTestsBundle\Configuration\CatalogInterface;
use Akeneo\Test\IntegrationTestsBundle\Security\SystemUserAuthenticator;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

/**
 * @author    Mathias METAYER <mathias.metayer@akeneo.com>
 * @copyright 2017 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
abstract class AbstractTestCase extends KernelTestCase
{
    /** @var CatalogInterface */
    protected $catalog;

    protected function setUp()
    {
        static::bootKernel(['debug' => false]);
        $authenticator = new SystemUserAuthenticator(static::$kernel->getContainer());
        $authenticator->createSystemUser();
        $this->catalog = $this->get('akeneo_integration_tests.configuration.catalog');

        if (null !== $this->getConfiguration()) {
            static::$kernel->getContainer()->set(
                'akeneo_integration_tests.catalog.configuration',
                $this->getConfiguration()
            );
            $fixturesLoader = $this->get('akeneo_integration_tests.loader.fixtures_loader');
            $fixturesLoader->load();
        }
    }

    /**
     * {@inheritdoc}
     */
    protected function tearDown()
    {
        $connectionCloser = $this->get(
            'akeneo_integration_tests.doctrine.connection.connection_closer'
        );
        $connectionCloser->closeConnections();
        parent::tearDown();
    }

    /**
     * @return Configuration
     */
    protected function getConfiguration(): ?Configuration
    {
        return null;
    }

    /**
     * @param $serviceName
     *
     * @return object
     */
    protected function get($serviceName)
    {
        return static::$kernel->getContainer()->get($serviceName);
    }

    /**
     * @param $serviceName
     *
     * @return mixed
     */
    protected function getParameter($serviceName)
    {
        return static::$kernel->getContainer()->getParameter($serviceName);
    }
}
