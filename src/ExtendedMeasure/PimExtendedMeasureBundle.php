<?php

namespace Pim\Bundle\ExtendedMeasureBundle;

use Pim\Bundle\ExtendedMeasureBundle\DependencyInjection\MeasuresCompilerPass;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\HttpKernel\Bundle\Bundle;

/**
 * @author Romain Monceau <romain@akeneo.com>
 */
class PimExtendedMeasureBundle extends Bundle
{
    /**
     * {@inheritdoc}
     */
    public function build(ContainerBuilder $container)
    {
        parent::build($container);
        $measuresConfigDir = __DIR__ . '/Resources/config/measures';
        $container->addCompilerPass(new MeasuresCompilerPass($measuresConfigDir));
    }
}
