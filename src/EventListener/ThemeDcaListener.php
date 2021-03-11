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
class ThemeDcaListener
{
    private Connection $connection;

    public function __construct(Connection $connection)
    {
        $this->connection = $connection;
    }

    public function __invoke($dc)
    {
        if (!$dc instanceof DataContainer) {
            return;
        }

        if ('edit' !== Input::get('act')) {
            return;
        }

        $this->alterEditMask((int) $dc->id);
    }

    private function alterEditMask(int $id): void
    {
        $layout = $this->connection
            ->executeQuery('SELECT id FROM tl_theme WHERE id=:id AND alias IS NOT NULL', ['id' => $id]);

        if (false === $layout->fetchOne()) {
            return;
        }

        $GLOBALS['TL_DCA']['tl_theme']['fields']['name']['eval']['readonly'] = true;

        PaletteManipulator::create()
            ->removeField('author')
            ->removeField('folders')
            ->removeField('screenshot')
            ->removeField('templates')
            ->removeField('vars')
            ->applyToPalette('default', 'tl_theme');
    }
}
