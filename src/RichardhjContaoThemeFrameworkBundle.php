<?php

declare(strict_types=1);

/*
 * This file is part of richardhj/contao-theme-framework.
 *
 * (c) Richard Henkenjohann <richardhenkenjohann@googlemail.com>
 *
 * @license MIT
 */

namespace Richardhj\ContaoThemeFramework;

use Richardhj\ContaoThemeFramework\DependencyInjection\CompilerPass\AddAssetsPackagesPass;
use Richardhj\ContaoThemeFramework\DependencyInjection\RichardhjContaoThemeFrameworkExtension;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Extension\ExtensionInterface;
use Symfony\Component\HttpKernel\Bundle\Bundle;

class RichardhjContaoThemeFrameworkBundle extends Bundle
{
    public function getPath(): string
    {
        return \dirname(__DIR__);
    }

    public function getContainerExtension(): ?ExtensionInterface
    {
        return new RichardhjContaoThemeFrameworkExtension();
    }

    public function build(ContainerBuilder $container): void
    {
        $container->addCompilerPass(new AddAssetsPackagesPass());
    }
}
