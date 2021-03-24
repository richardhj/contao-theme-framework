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

use Symfony\Component\DependencyInjection\ChildDefinition;
use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Definition;
use Symfony\Component\DependencyInjection\Reference;
use Symfony\Component\Finder\Finder;
use Webmozart\PathUtil\Path;

/**
 * Creates a assets.packages definition for each public folder within a theme.
 */
class AddAssetsPackagesPass implements CompilerPassInterface
{
    public function process(ContainerBuilder $container): void
    {
        if (!file_exists($container->getParameter('kernel.project_dir').'/themes')) {
            return;
        }

        if (!$container->hasDefinition('assets.packages')) {
            return;
        }

        $this->addThemes($container);
    }

    private function addThemes(ContainerBuilder $container): void
    {
        $rootDir = $container->getParameter('kernel.project_dir');
        $packages = $container->getDefinition('assets.packages');
        $context = new Reference('contao.assets.assets_context');

        if ($container->hasDefinition('assets._version_default')) {
            $version = new Reference('assets._version_default');
        } else {
            $version = new Reference('assets.empty_version_strategy');
        }

        $finder = new Finder();
        $finder->directories()
            ->in($rootDir.'/themes')
            ->path('public')
            ->depth('== 1')
        ;

        foreach ($finder as $theme) {
            $packageVersion = $version;
            $packageName = $theme->getRelativePath();
            $serviceId = 'assets._package_'.$packageName;
            $path = Path::join($rootDir, 'themes', $theme->getRelativePathname());
            $basePath = Path::join('themes', $theme->getRelativePath());

            if (is_file($manifestPath = Path::join($path, 'manifest.json'))) {
                $def = new ChildDefinition('assets.json_manifest_version_strategy');
                $def->replaceArgument(0, $manifestPath);

                $container->setDefinition('assets._version_'.$packageName, $def);
                $packageVersion = new Reference('assets._version_'.$packageName);
            }

            $container->setDefinition($serviceId, $this->createPackageDefinition($basePath, $packageVersion, $context));
            $packages->addMethodCall('addPackage', [$packageName, new Reference($serviceId)]);
        }
    }

    private function createPackageDefinition(string $basePath, Reference $version, Reference $context): Definition
    {
        $package = new ChildDefinition('assets.path_package');
        $package
            ->setPublic(false)
            ->replaceArgument(0, $basePath)
            ->replaceArgument(1, $version)
            ->replaceArgument(2, $context)
        ;

        return $package;
    }
}
