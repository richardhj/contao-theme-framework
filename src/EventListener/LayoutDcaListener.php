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

use Contao\CoreBundle\DataContainer\PaletteManipulator;
use Contao\DataContainer;
use Contao\Input;
use Doctrine\DBAL\Connection;

/**
 * Removes fields from the DCA palettes.
 */
class LayoutDcaListener
{
    private Connection $connection;

    public function __construct(Connection $connection)
    {
        $this->connection = $connection;
    }

    public function __invoke($dc): void
    {
        if (!$dc instanceof DataContainer) {
            return;
        }

        if ('edit' === Input::get('act')) {
            $this->alterEditMask((int) $dc->id);

            return;
        }

        $GLOBALS['TL_DCA']['tl_layout']['config']['closed'] = true;

        unset(
            $GLOBALS['TL_DCA']['tl_layout']['list']['operations']['delete'],
            $GLOBALS['TL_DCA']['tl_layout']['list']['operations']['copy'],
            $GLOBALS['TL_DCA']['tl_layout']['list']['operations']['cut']
        );
    }

    private function alterEditMask(int $id): void
    {
        $layout = $this->connection
            ->executeQuery('SELECT id FROM tl_layout WHERE id=:id AND alias IS NOT NULL', ['id' => $id]);

        if (false === $layout->fetchOne()) {
            return;
        }

        $GLOBALS['TL_DCA']['tl_layout']['fields']['name']['eval']['readonly'] = true;

        PaletteManipulator::create()
            ->removeField('rows')
            ->removeField('cols')
            ->removeField('sections')
            ->removeField('lightboxSize')
            ->removeField('defaultImageDensities')
            ->removeField('framework')
            ->removeField('stylesheet')
            ->removeField('external')
            ->removeField('loadingOrder')
            ->removeField('analytics')
            ->removeField('combineScripts')
            ->removeField('scripts')
            ->removeField('externalJs')
            ->removeField('script')
            ->removeField('static')
            ->removeField('onload')
            ->removeField('cssClass')
            ->removeField('titleTag')
            ->removeField('addJQuery')
            ->removeField('addMooTools')
            ->removeField('template')
            ->removeField('minifyMarkup')
            ->removeField('viewport')
            ->removeField('head')
            ->applyToPalette('default', 'tl_layout');
    }
}
