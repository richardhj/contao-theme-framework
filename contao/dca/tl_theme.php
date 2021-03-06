<?php

declare(strict_types=1);

/*
 * This file is part of richardhj/contao-theme-framework.
 *
 * (c) Richard Henkenjohann <richardhenkenjohann@googlemail.com>
 *
 * @license MIT
 */

$GLOBALS['TL_DCA']['tl_theme']['config']['sql']['keys']['alias'] = 'unique';

$GLOBALS['TL_DCA']['tl_theme']['fields']['alias']['sql'] = 'varchar(128) default NULL';
$GLOBALS['TL_DCA']['tl_theme']['fields']['manifestHash']['sql'] = 'varchar(40) default NULL';
