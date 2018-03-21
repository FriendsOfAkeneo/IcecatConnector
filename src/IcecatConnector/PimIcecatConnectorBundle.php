<?php

namespace Pim\Bundle\IcecatConnectorBundle;

use Pim\Bundle\ExtendedMeasureBundle\DependencyInjection\MeasuresCompilerPass;
use Pim\Bundle\IcecatConnectorBundle\DependencyInjection\OroConfigCompilerPass;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\HttpKernel\Bundle\Bundle;

/**
 * @author JM Leroux <jean-marie.leroux@akeneo.com>
 */
class PimIcecatConnectorBundle extends Bundle
{
    /**
     * {@inheritdoc}
     */
    public function build(ContainerBuilder $container)
    {
        parent::build($container);
        $measuresConfigDir = __DIR__ . '/Resources/config/measures';
        $container->addCompilerPass(new MeasuresCompilerPass($measuresConfigDir));
        $container->addCompilerPass(new OroConfigCompilerPass());
    }
}
