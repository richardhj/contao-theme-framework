<?php

declare(strict_types=1);

/*
 * This file is part of richardhj/contao-theme-framework.
 *
 * (c) Richard Henkenjohann <richardhenkenjohann@googlemail.com>
 *
 * @license MIT
 */

namespace Richardhj\ContaoThemeFramework\DependencyInjection;

use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use Symfony\Component\Config\Definition\ConfigurationInterface;

final class Configuration implements ConfigurationInterface
{
    public function getConfigTreeBuilder(): TreeBuilder
    {
        $treeBuilder = new TreeBuilder('contao_theme_framework');
        $treeBuilder->getRootNode()
            ->children()
                ->scalarNode('themes_path')
                    ->defaultValue('themes')
                    ->info('The path to the themes directory, relative to the root dir.')
                ->end()
            ->end()
        ;

        return $treeBuilder;
    }
}
