<?php

declare(strict_types=1);

/*
 * This file is part of richardhj/contao-theme-framework.
 *
 * (c) Richard Henkenjohann <richardhenkenjohann@googlemail.com>
 *
 * @license MIT
 */

namespace Richardhj\ContaoThemeFramework\Configuration;

use Contao\Image\ResizeConfiguration;
use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use Symfony\Component\Config\Definition\ConfigurationInterface;

class ThemeManifestConfiguration implements ConfigurationInterface
{
    public function getConfigTreeBuilder()
    {
        $treeBuilder = new TreeBuilder('manifest');
        $treeBuilder->getRootNode()
            ->children()
                ->arrayNode('theme')
                    ->children()
                        ->scalarNode('name')->isRequired()->end()
                    ->end()
                ->end()
                ->arrayNode('layouts')
                    ->arrayPrototype()
                        ->children()
                            ->scalarNode('name')->isRequired()->end()
                            ->scalarNode('template')->end()
                        ->end()
                        // Keep arbitrary configuration
                        ->ignoreExtraKeys(false)
                    ->end()
                    ->validate()
                        // Merge the default layout
                        ->always()
                        ->then(fn (array $layouts) => array_map(fn (array $layout) => array_merge($layouts['_default'] ?? [], $layout), $layouts))
                    ->end()
                ->end()
                ->append($this->addImageSizesNode())
            ->end()
        ;

        return $treeBuilder;
    }

    private function addImageSizesNode()
    {
        $treeBuilder = new TreeBuilder('image_sizes');

        return $treeBuilder->getRootNode()
            ->useAttributeAsKey('name')
            ->validate()
                ->always(
                    static function (array $value): array {
                        static $reservedImageSizeNames = [
                            ResizeConfiguration::MODE_BOX,
                            ResizeConfiguration::MODE_PROPORTIONAL,
                            ResizeConfiguration::MODE_CROP,
                            'left_top',
                            'center_top',
                            'right_top',
                            'left_center',
                            'center_center',
                            'right_center',
                            'left_bottom',
                            'center_bottom',
                            'right_bottom',
                        ];

                        foreach (array_keys($value) as $name) {
                            if (preg_match('/^\d+$/', (string) $name)) {
                                throw new \InvalidArgumentException(sprintf('The image size name "%s" cannot contain only digits', $name));
                            }

                            if (\in_array($name, $reservedImageSizeNames, true)) {
                                throw new \InvalidArgumentException(sprintf('"%s" is a reserved image size name (reserved names: %s)', $name, implode(', ', $reservedImageSizeNames)));
                            }

                            if (preg_match('/[^a-z0-9_]/', (string) $name)) {
                                throw new \InvalidArgumentException(sprintf('The image size name "%s" must consist of lowercase letters, digits and underscores only', $name));
                            }
                        }

                        return $value;
                    }
                )
            ->end()
            ->arrayPrototype()
                ->children()
                    ->integerNode('width')
                    ->end()
                    ->integerNode('height')
                    ->end()
                    ->enumNode('resize_mode')
                        ->values([
                            ResizeConfiguration::MODE_CROP,
                            ResizeConfiguration::MODE_BOX,
                            ResizeConfiguration::MODE_PROPORTIONAL,
                        ])
                    ->end()
                    ->integerNode('zoom')
                        ->min(0)
                        ->max(100)
                    ->end()
                    ->scalarNode('css_class')
                    ->end()
                    ->booleanNode('lazy_loading')
                    ->end()
                    ->scalarNode('densities')
                    ->end()
                    ->scalarNode('sizes')
                    ->end()
                    ->booleanNode('skip_if_dimensions_match')
                        ->info('If the output dimensions match the source dimensions, the image will not be processed. Instead, the original file will be used.')
                    ->end()
                    ->arrayNode('formats')
                        ->info('Allows to convert one image format to another or to provide additional image formats for an image (e.g. WebP).')
                        ->example(['jpg' => ['webp', 'jpg'], 'gif' => ['png']])
                        ->useAttributeAsKey('source')
                        ->arrayPrototype()
                            ->beforeNormalization()->castToArray()->end()
                            ->scalarPrototype()->end()
                        ->end()
                    ->end()
                    ->arrayNode('items')
                        ->arrayPrototype()
                            ->children()
                                ->integerNode('width')
                                ->end()
                                ->integerNode('height')
                                ->end()
                                ->enumNode('resize_mode')
                                    ->values([
                                        ResizeConfiguration::MODE_CROP,
                                        ResizeConfiguration::MODE_BOX,
                                        ResizeConfiguration::MODE_PROPORTIONAL,
                                    ])
                                ->end()
                                ->integerNode('zoom')
                                    ->min(0)
                                    ->max(100)
                                ->end()
                                ->scalarNode('media')
                                ->end()
                                ->scalarNode('densities')
                                ->end()
                                ->scalarNode('sizes')
                                ->end()
                            ->end()
                        ->end()
                    ->end()
                ->end()
            ->end()
        ;
    }
}
