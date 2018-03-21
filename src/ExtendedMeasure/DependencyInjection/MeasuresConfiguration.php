<?php
namespace Pim\Bundle\ExtendedMeasureBundle\DependencyInjection;

use Akeneo\Bundle\MeasureBundle\DependencyInjection\Configuration;
use Symfony\Component\Config\Definition\Builder\TreeBuilder;

/**
 * @author    JM Leroux <jean-marie.leroux@akeneo.com>
 * @copyright 2016 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/MIT MIT
 */
class MeasuresConfiguration extends Configuration
{
    /**
     * {@inheritDoc}
     */
    public function getConfigTreeBuilder()
    {
        $treeBuilder = new TreeBuilder();
        $rootNode = $treeBuilder->root('measures_config');

        $rootNode->useAttributeAsKey('family')
            ->prototype('array')
            ->children()
                ->scalarNode('standard')->isRequired()->end()
                ->arrayNode('units')->prototype('array')
                ->children()
                    ->append($this->addConvertNode())
                    ->scalarNode('symbol')->isRequired()->end()
                    ->scalarNode('name')->end()
                    ->scalarNode('unece_code')->end()
                    ->arrayNode('alternative_symbols')
                        ->prototype('scalar')->end()
                    ->end()
                ->end()
            ->end()
            ->end();

        return $treeBuilder;
    }
}
