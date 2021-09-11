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

use Contao\CoreBundle\Twig\Loader\ThemeNamespace as BaseThemeNamespace;

/**
 * Override the Twig namespace for our template directories.
 */
class ThemeNamespace extends BaseThemeNamespace
{
    private BaseThemeNamespace $decoratedLoader;

    public function __construct(BaseThemeNamespace $decoratedLoader)
    {
        $this->decoratedLoader = $decoratedLoader;
    }

    public function generateSlug(string $relativePath): string
    {
        // Normalize `themes/foo/templates` paths to `foo`.
        $slug = preg_replace('/^\.\.\/themes\/(.+?)\/templates$/', '$1', $relativePath);

        // CamelCase slug
        $slug = str_replace('_', '', ucwords($slug, '_'));

        return $this->decoratedLoader->generateSlug($slug);
    }
}
