<?php

namespace Pim\Bundle\IcecatConnectorBundle\DependencyInjection;

use Oro\Bundle\ConfigBundle\DependencyInjection\SettingsBuilder;
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

        $rootNode = $treeBuilder->root('pim_icecat_connector');

        $children = $rootNode->children()
            ->scalarNode('credentials_username')->end()
            ->scalarNode('credentials_password')->end()
            ->scalarNode('ean_attribute')->end()
            ->scalarNode('description')->end()
            ->scalarNode('short_description')->end()
            ->scalarNode('summary_description')->end()
            ->scalarNode('short_summary_description')->end()
            ->scalarNode('pictures')->end();

        $children->end();
        $rootNode->end();

        SettingsBuilder::append(
            $rootNode,
            [
                'credentials_username' => ['value' => null],
                'credentials_password' => ['value' => null],
                'ean_attribute' => ['value' => null],
                'description' => ['value' => null],
                'short_description' => ['value' => null],
                'summary_description' => ['value' => null],
                'short_summary_description' => ['value' => null],
                'pictures' => ['value' => null],
            ]
        );

        return $treeBuilder;
    }
}
