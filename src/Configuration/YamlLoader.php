<?php

declare(strict_types=1);

/*
 * This file is part of richardhj/contao-theme-framework.
 *
 * (c) Richard Henkenjohann <richardhenkenjohann@googlemail.com>
 *
 * @license MIT
 */

namespace Richardhj\ContaoThemeFramework\Configuration;

use Symfony\Component\Config\Loader\Loader;
use Symfony\Component\Yaml\Yaml;

class YamlLoader extends Loader
{
    public function load($resource, $type = null)
    {
        return Yaml::parse(file_get_contents($resource));
    }

    public function supports($resource, $type = null)
    {
        return \is_string($resource) && \in_array(pathinfo($resource, \PATHINFO_EXTENSION), ['yaml', 'yml'], true);
    }
}
