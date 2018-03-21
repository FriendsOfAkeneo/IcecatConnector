<?php

namespace Pim\Bundle\ExtendedMeasureBundle\DependencyInjection;

use Symfony\Component\Config\Definition\Processor;
use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\Finder\Finder;
use Symfony\Component\Finder\SplFileInfo;
use Symfony\Component\Yaml\Yaml;

/**
 * Load all measures from a directory
 *
 * @author JM Leroux <jean-marie.leroux@akeneo.com>
 */
class MeasuresCompilerPass implements CompilerPassInterface
{
    /** @var string */
    protected $configDirectory;

    /**
     * @param string $configDirectory Directory of measures configurations
     */
    public function __construct($configDirectory)
    {
        $this->configDirectory = $configDirectory;
    }

    /**
     * {@inheritdoc}
     */
    public function process(ContainerBuilder $container)
    {
        $measuresConfig = [];

        $measuresFinder = new Finder();
        $measuresFinder->files()->in($this->configDirectory)->name(('*.yml'));

        foreach ($measuresFinder as $file) {
            $measuresConfig = $this->processFile($file, $measuresConfig);
        }

        $preset = $container->getParameter('akeneo_measure.measures_config');
        $measuresConfig = array_replace_recursive($preset, $measuresConfig);

        $processor = new Processor();
        $configTree = new MeasuresConfiguration();
        $measuresConfig['measures_config'] = $processor->processConfiguration($configTree, $measuresConfig);

        $container->setParameter('akeneo_measure.measures_config', $measuresConfig);
    }

    /**
     * @param SplFileInfo $file
     * @param array       $measuresConfig
     *
     * @return array
     */
    protected function processFile(SplFileInfo $file, array $measuresConfig)
    {
        $entities = Yaml::parse($file->getContents());

        foreach ($entities['measures_config'] as $family => $familyConfig) {
            if (isset($measuresConfig['measures_config'][$family])) {
                $measuresConfig['measures_config'][$family]['units'] =
                    array_merge_recursive(
                        $measuresConfig['measures_config'][$family]['units'],
                        $familyConfig['units']
                    );
            } else {
                $measuresConfig['measures_config'][$family] = $familyConfig;
            }
        }

        return $measuresConfig;
    }
}
