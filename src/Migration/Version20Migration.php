<?php

declare(strict_types=1);

/*
 * This file is part of richardhj/contao-theme-framework.
 *
 * (c) Richard Henkenjohann <richardhenkenjohann@googlemail.com>
 *
 * @license MIT
 */

namespace Richardhj\ContaoThemeFramework\Migration;

use Contao\CoreBundle\Migration\MigrationInterface;
use Contao\CoreBundle\Migration\MigrationResult;
use Contao\CoreBundle\Twig\Loader\ContaoFilesystemLoaderWarmer;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\Finder\Finder;
use Symfony\Component\Yaml\Yaml;

/**
 * Performs the update to version 2.0.
 */
class Version20Migration implements MigrationInterface
{
    private ContaoFilesystemLoaderWarmer $filesystemLoaderWarmer;
    private string $rootDir;
    private Filesystem $filesystem;

    public function __construct(ContaoFilesystemLoaderWarmer $filesystemLoaderWarmer, string $rootDir, Filesystem $filesystem)
    {
        $this->filesystemLoaderWarmer = $filesystemLoaderWarmer;
        $this->rootDir = $rootDir;
        $this->filesystem = $filesystem;
    }

    public function getName(): string
    {
        return 'Upgrade to richardhj/contao-theme-framework v2.0';
    }

    public function shouldRun(): bool
    {
        if (!file_exists($this->rootDir.'/themes')) {
            return false;
        }

        $manifests = (new Finder())
            ->files()
            ->in($this->rootDir.'/themes')
            ->name('theme.yml')
            ->getIterator()
        ;

        foreach ($manifests as $manifest) {
            $yaml = Yaml::parse($manifest->getContents());

            if (isset($yaml['theme']['layouts']) && !isset($yaml['layouts'])) {
                return true;
            }
        }

        return false;
    }

    public function run(): MigrationResult
    {
        $manifests = (new Finder())
            ->files()
            ->in($this->rootDir.'/themes')
            ->name('theme.yml')
            ->getIterator()
        ;

        // Transform manifest
        foreach ($manifests as $manifest) {
            $yaml = Yaml::parse($manifest->getContents());

            if (isset($yaml['theme']['layouts']) && !isset($yaml['layouts'])) {
                $yaml['layouts'] = $yaml['theme']['layouts'];

                unset($yaml['theme']['layouts']);
            }

            $this->filesystem->dumpFile($manifest->getRealPath(), Yaml::dump($yaml, 4));
        }

        // Register template paths
        $this->filesystemLoaderWarmer->refresh();

        return new MigrationResult(true, 'Upgrade was successful.');
    }
}
