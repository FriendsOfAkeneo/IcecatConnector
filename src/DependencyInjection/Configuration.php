<?php

namespace Pim\Bundle\IcecatConnectorBundle\DependencyInjection;

use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use Symfony\Component\Config\Definition\ConfigurationInterface;

/**
 * @author    Jean-Marie Leroux <jean-marie.leroux@akeneo.com>
 * @copyright 2016 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class Configuration implements ConfigurationInterface
{
    /**
     * {@inheritdoc}
     */
    public function getConfigTreeBuilder()
    {
        $treeBuilder = new TreeBuilder();

        $root = $treeBuilder->root('pim_icecat_connector');

        $children = $root->children();

        $children->arrayNode('settings')
            ->children()
                ->arrayNode('ean_attribute')
                    ->children()
                        ->scalarNode('value')->end()
                        ->scalarNode('scope')->end()
                    ->end()
                ->end()
                ->arrayNode('description')
                    ->children()
                        ->scalarNode('value')->end()
                        ->scalarNode('scope')->end()
                    ->end()
                ->end()
                ->arrayNode('short_description')
                    ->children()
                        ->scalarNode('value')->end()
                        ->scalarNode('scope')->end()
                    ->end()
                ->end()
                ->arrayNode('summary_description')
                    ->children()
                        ->scalarNode('value')->end()
                        ->scalarNode('scope')->end()
                    ->end()
                ->end()
                ->arrayNode('short_summary_description')
                    ->children()
                        ->scalarNode('value')->end()
                        ->scalarNode('scope')->end()
                    ->end()
                ->end()
                ->arrayNode('pictures')
                    ->children()
                        ->scalarNode('value')->end()
                        ->scalarNode('scope')->end()
                    ->end()
                ->end()
            ->end()
        ->end();

        $children->end();

        $root->end();

        return $treeBuilder;
    }
}
