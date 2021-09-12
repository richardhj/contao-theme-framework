<?php

declare(strict_types=1);

/*
 * This file is part of richardhj/contao-theme-framework.
 *
 * (c) Richard Henkenjohann <richardhenkenjohann@googlemail.com>
 *
 * @license MIT
 */

namespace Richardhj\ContaoThemeFramework\Encore;

use Contao\PageModel;
use Symfony\WebpackEncoreBundle\Asset\EntrypointLookup;
use Symfony\WebpackEncoreBundle\Asset\EntrypointLookupCollectionInterface;
use Symfony\WebpackEncoreBundle\Asset\EntrypointLookupInterface;
use Symfony\WebpackEncoreBundle\Exception\UndefinedBuildException;
use Webmozart\PathUtil\Path;

/**
 * Aggregate the different Encore entry points that are configured via theme.yml manifests.
 *
 * Retrieve the EntrypointLookup instance from the given key.
 *
 * @internal
 */
class EncoreEntrypointLookupCollection implements EntrypointLookupCollectionInterface
{
    private string $webDir;

    public function __construct(string $webDir)
    {
        $this->webDir = $webDir;
    }

    /**
     * Returns the correct entrypoint lookup.
     *
     * @throws UndefinedBuildException if the theme does not exist
     */
    public function getEntrypointLookup(string $buildName = null): EntrypointLookupInterface
    {
        if (null === $buildName || '_default' === $buildName) {
            /** @var PageModel $pageModel */
            $pageModel = $GLOBALS['objPage'];

            $buildName = $pageModel->theme;
        }

        $themePath = Path::join($this->webDir, 'themes', $buildName, 'entrypoints.json');

        return new EntrypointLookup($themePath);
    }
}
