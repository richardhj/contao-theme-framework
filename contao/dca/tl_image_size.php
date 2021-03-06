<?php

declare(strict_types=1);

/*
 * This file is part of richardhj/contao-theme-framework.
 *
 * (c) Richard Henkenjohann <richardhenkenjohann@googlemail.com>
 *
 * @license MIT
 */

$GLOBALS['TL_DCA']['tl_theme']['config']['sql']['keys']['pid,alias'] = 'unique';

$GLOBALS['TL_DCA']['tl_image_size']['fields']['alias']['sql'] = 'varchar(128) default NULL';
