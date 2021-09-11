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
use Contao\ImageSizeModel;
use Contao\Input;

/**
 * Removes fields from the DCA palettes.
 */
class ImageSizeDcaListener
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
        // dd($imageSize);
        if (!$imageSize->alias) {
            return;
        }

        $GLOBALS['TL_DCA']['tl_image_size']['config']['closed'] = true;

        unset(
            $GLOBALS['TL_DCA']['tl_image_size']['list']['operations']['delete'],
            $GLOBALS['TL_DCA']['tl_image_size']['list']['operations']['copy'],
            $GLOBALS['TL_DCA']['tl_image_size']['list']['operations']['cut']
        );
    }

    private function alterEditMask(int $imageSizeId): void
    {
        $imageSize = ImageSizeModel::findByPk($imageSizeId)->getRelated('pid');
        if (!$imageSize->alias) {
            return;
        }

        foreach ([
            'name',
            'width',
            'height',
            'resizeMode',
            'zoom',
            'densities',
            'sizes',
            'lazyLoading',
            'formats',
            'cssClass',
            'skipIfDimensionsMatch',
        ] as $field) {
            $GLOBALS['TL_DCA']['tl_image_size']['fields'][$field]['eval']['readonly'] = true;
            $GLOBALS['TL_DCA']['tl_image_size']['fields'][$field]['eval']['disabled'] = true;
        }
    }
}
