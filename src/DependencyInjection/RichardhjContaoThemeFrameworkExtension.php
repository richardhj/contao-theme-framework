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

use Richardhj\ContaoThemeFramework\Migration\ThemeMigration;
use Symfony\Component\Config\FileLocator;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Loader\YamlFileLoader;
use Symfony\Component\HttpKernel\DependencyInjection\Extension;

class RichardhjContaoThemeFrameworkExtension extends Extension
{
    public function getAlias(): string
    {
        return 'contao_theme_framework';
    }

    public function load(array $configs, ContainerBuilder $container): void
    {
        $loader = new YamlFileLoader($container, new FileLocator(__DIR__.'/../../config'));
        $loader->load('services.yml');

        if ($container->hasDefinition('webpack_encore.tag_renderer')) {
            $loader->load('encore.yml');
        }

        $config = $this->processConfiguration(new Configuration(), $configs);
        $container->getDefinition(ThemeMigration::class)->replaceArgument(3, $config['themes_path']);
    }
}
