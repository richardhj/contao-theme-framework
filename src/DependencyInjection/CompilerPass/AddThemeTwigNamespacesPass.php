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
use Symfony\Component\Finder\Finder;
use Webmozart\PathUtil\Path;

class AddThemeTwigNamespacesPass implements CompilerPassInterface
{
    public function process(ContainerBuilder $container): void
    {
        if (!$container->has('twig.loader.native_filesystem')) {
            return;
        }

        $twigLoader = $container->getDefinition('twig.loader.native_filesystem');

        $themes = (new Finder())
            ->directories()
            ->in($container->getParameter('kernel.project_dir').'/themes')
            ->path('templates')
            ->depth('== 1')
            ->getIterator()
        ;

        foreach ($themes as $theme) {
            $path = Path::join('themes', $theme->getRelativePathname());
            $themeName = $theme->getRelativePath();

            $twigLoader->addMethodCall('addPath', [$path, $themeName]);
        }
    }
}
