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

use Contao\DataContainer;
use Contao\ImageSizeItemModel;
use Contao\ImageSizeModel;
use Contao\Input;

/**
 * Removes fields from the DCA palettes.
 */
class ImageSizeItemDcaListener
{
    public function __invoke($dc): void
    {
        if (!$dc instanceof DataContainer) {
            return;
        }

        if ('edit' === Input::get('act')) {
            $this->alterEditMask((int) $dc->id);

            return;
        }

        $this->alterListView((int) $dc->id);
    }

    private function alterListView(int $imageSizeId)
    {
        $imageSize = ImageSizeModel::findByPk($imageSizeId);
        if (!$imageSize->alias) {
            return;
        }

        $GLOBALS['TL_DCA']['tl_image_size_item']['config']['closed'] = true;
        $GLOBALS['TL_DCA']['tl_image_size_item']['config']['notEditable'] = true;

        unset(
            $GLOBALS['TL_DCA']['tl_image_size_item']['list']['operations']['edit'],
            $GLOBALS['TL_DCA']['tl_image_size_item']['list']['operations']['delete'],
            $GLOBALS['TL_DCA']['tl_image_size_item']['list']['operations']['copy'],
            $GLOBALS['TL_DCA']['tl_image_size_item']['list']['operations']['cut']
        );
    }

    private function alterEditMask(int $imageSizeId): void
    {
        $imageSize = ImageSizeItemModel::findByPk($imageSizeId)->getRelated('pid');
        if (!$imageSize->alias) {
            return;
        }

        $GLOBALS['TL_DCA']['tl_image_size_item']['config']['notEditable'] = true;
    }
}
