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

use Contao\LayoutModel;
use Contao\PageModel;
use Contao\PageRegular;

/**
 * Add the current theme name to page model.
 */
class PageLayoutListener
{
    public function __invoke(PageModel $pageModel, LayoutModel $layout, PageRegular $pageRegular)
    {
        $theme = $layout->getRelated('pid');
        if (null === $theme) {
            return;
        }

        $pageModel->theme = $theme->alias ?? '';
    }
}
