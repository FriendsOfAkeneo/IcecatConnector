<?php

namespace spec\Pim\Bundle\ExtendedMeasureBundle\DependencyInjection;

use PhpSpec\ObjectBehavior;
use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;

/**
 * @author JM Leroux <jean-marie.leroux@akeneo.com>
 */
class MeasuresCompilerPassSpec extends ObjectBehavior
{
    public function let()
    {
        $configDirectory = __DIR__ . '/../Resources/measures';
        $this->beConstructedWith($configDirectory);
    }

    public function it_is_initializable()
    {
        $this->shouldImplement(CompilerPassInterface::class);
    }

    public function it_processes_configuration(ContainerBuilder $container)
    {
        $container->getParameter('akeneo_measure.measures_config')
            ->willReturn([]);

        $expectedConfig = require (__DIR__ . '/../Resources/measures/merged_configuration.php');

        $container->setParameter('akeneo_measure.measures_config', $expectedConfig)
            ->shouldBeCalled();

        $this->process($container);
    }
}
