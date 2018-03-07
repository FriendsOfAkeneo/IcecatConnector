<?php

namespace Pim\Bundle\IcecatConnectorBundle\Tests;

/**
 * App kernel for the integration tests.
 *
 * @author    Mathias Métayer <mathias.metayer@akeneo.com>
 * @copyright 2017 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class AppKernelTestEe extends \AppKernel
{
    /**
     * {@inheritdoc}
     */
    protected function registerProjectBundles(): array
    {
        return [
            new \Akeneo\Test\IntegrationTestsBundle\AkeneoIntegrationTestsBundle(),
            new \AkeneoEnterprise\Test\IntegrationTestsBundle\AkeneoEnterpriseIntegrationTestsBundle(),
            new \Pim\Bundle\ExtendedMeasureBundle\PimExtendedMeasureBundle(),
            new \Pim\Bundle\ExtendedAttributeTypeBundle\PimExtendedAttributeTypeBundle(),
            new \Pim\Bundle\IcecatConnectorBundle\PimIcecatConnectorBundle(),
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function getName(): string
    {
        if (null === $this->name) {
            $this->name =  parent::getName() . '_test_ee';
        }

        return $this->name;
    }
}
