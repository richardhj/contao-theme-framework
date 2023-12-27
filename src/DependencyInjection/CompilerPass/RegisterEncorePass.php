<?php

declare(strict_types=1);

/*
 * This file is part of richardhj/contao-theme-framework.
 *
 * (c) Richard Henkenjohann <richardhenkenjohann@googlemail.com>
 *
 * @license MIT
 */

namespace Richardhj\ContaoThemeFramework\DependencyInjection\CompilerPass;

use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Definition;

/**
 * Creates the contao.themes.tag_renderer service
 */
class RegisterEncorePass implements CompilerPassInterface
{
    public function process(ContainerBuilder $container): void
    {
        if (!file_exists($container->getParameter('kernel.project_dir').'/themes')) {
            return;
        }

        if (!$container->hasDefinition('Symfony\WebpackEncoreBundle\Asset\TagRenderer')) {
            return;
        }

        $definition = new Definition('Symfony\WebpackEncoreBundle\Asset\TagRenderer', [
            '@Richardhj\ContaoThemeFramework\Encore\EncoreEntrypointLookupCollection',
            '@assets.packages',
            [ ],
            [ ],
            [ ],
            '@event_dispatcher'
        ]);

        $definition->addTag('kernel.reset', ['method'=>'reset']);

        $container->setDefinition('contao.themes.tag_renderer', $definition);
    }
}
