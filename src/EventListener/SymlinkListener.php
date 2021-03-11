<?php

declare(strict_types=1);

/*
 * This file is part of richardhj/contao-theme-framework.
 *
 * (c) Richard Henkenjohann <richardhenkenjohann@googlemail.com>
 *
 * @license MIT
 */

namespace Richardhj\ContaoThemeFramework\EventListener;

use Contao\CoreBundle\Event\GenerateSymlinksEvent;
use Symfony\Component\Finder\Finder;
use Webmozart\PathUtil\Path;

/**
 * Symlinks a theme's public folder (`theme/my_theme/public`) to the web directory.
 */
class SymlinkListener
{
    private string $rootDir;

    public function __construct(string $rootDir)
    {
        $this->rootDir = $rootDir;
    }

    public function __invoke(GenerateSymlinksEvent $event)
    {
        $finder = new Finder();
        $finder->directories()->in($this->rootDir.'/themes')->path('public')->depth('== 1');

        foreach ($finder as $theme) {
            $event->addSymlink(
                Path::join('themes', $theme->getRelativePathname()),
                Path::join('web', 'themes', $theme->getRelativePath())
            );
        }
    }
}
