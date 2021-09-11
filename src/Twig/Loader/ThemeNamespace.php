<?php

declare(strict_types=1);

/*
 * This file is part of richardhj/contao-theme-framework.
 *
 * (c) Richard Henkenjohann <richardhenkenjohann@googlemail.com>
 *
 * @license MIT
 */

namespace Richardhj\ContaoThemeFramework\Twig\Loader;

/**
 * Override the Twig namespace for our template directories.
 */
class ThemeNamespace extends \Contao\CoreBundle\Twig\Loader\ThemeNamespace
{
    private \Contao\CoreBundle\Twig\Loader\ThemeNamespace $decoratedLoader;

    public function __construct(\Contao\CoreBundle\Twig\Loader\ThemeNamespace $decoratedLoader)
    {
        $this->decoratedLoader = $decoratedLoader;
    }

    public function generateSlug(string $relativePath): string
    {
        // Normalize `themes/foo/templates` paths to `foo`.
        $relativePath = preg_replace('/^\.\.\/themes\/(.+?)\/templates$/', '$1', $relativePath);

        return $this->decoratedLoader->generateSlug($relativePath);
    }
}
