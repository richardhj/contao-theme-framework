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
use Richardhj\ContaoThemeFramework\DependencyInjection\CompilerPass\RegisterEncorePass;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\HttpKernel\Bundle\Bundle;

class RichardhjContaoThemeFrameworkBundle extends Bundle
{
    public function getPath(): string
    {
        return \dirname(__DIR__);
    }

    public function build(ContainerBuilder $container)
    {
        $container->addCompilerPass(new AddAssetsPackagesPass());
    }
}
