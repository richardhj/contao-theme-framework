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
use Doctrine\DBAL\Connection;
use Doctrine\DBAL\Exception;
use Doctrine\DBAL\FetchMode;
use Richardhj\ContaoThemeFramework\Configuration\ThemeManifestConfiguration;
use Richardhj\ContaoThemeFramework\Configuration\YamlLoader;
use Symfony\Component\Config\Definition\Processor;
use Symfony\Component\Config\Loader\DelegatingLoader;
use Symfony\Component\Config\Loader\LoaderResolver;
use Symfony\Component\Finder\Finder;

/**
 * Persists the themes defined in `themes/foobar/theme.yml` in the database.
 */
class ThemeMigration implements MigrationInterface
{
    private Connection $connection;
    private ContaoFilesystemLoaderWarmer $filesystemLoaderWarmer;
    private string $rootDir;

    public function __construct(Connection $connection, ContaoFilesystemLoaderWarmer $filesystemLoaderWarmer, string $rootDir)
    {
        $this->connection = $connection;
        $this->rootDir = $rootDir;
        $this->filesystemLoaderWarmer = $filesystemLoaderWarmer;
    }

    public function getName(): string
    {
        return 'Install Contao themes from theme.yml manifests';
    }

    public function shouldRun(): bool
    {
        if (!file_exists($this->rootDir.'/themes')) {
            return false;
        }

        $manifests = (new Finder())
            ->files()
            ->in($this->rootDir.'/themes')
            ->name(['theme.yml', 'theme.yaml', 'theme.xml'])
            ->getIterator()
        ;

        try {
            // Check fields exist
            $this->connection->executeQuery("SELECT id FROM tl_theme WHERE alias=''");
            $this->connection->executeQuery("SELECT id FROM tl_layout WHERE alias=''");

            // Check for manifest changes
            foreach ($manifests as $manifest) {
                $manifestHash = md5_file($manifest->getRealPath());

                $persistedHash = $this->connection
                    ->executeQuery('SELECT manifestHash FROM tl_theme WHERE alias=:alias', [
                        'alias' => $manifest->getRelativePath(),
                    ])->fetchColumn();

                if ($persistedHash !== $manifestHash) {
                    return true;
                }
            }
        } catch (Exception $e) {
            return false;
        }

        return false;
    }

    public function run(): MigrationResult
    {
        $manifests = (new Finder())
            ->files()
            ->in($this->rootDir.'/themes')
            ->name(['theme.yml', 'theme.yaml'])
            ->getIterator()
        ;

        $installed = 0;
        $aliases = [];
        foreach ($manifests as $manifest) {
            $themeName = $manifest->getRelativePath();
            $aliases[] = $themeName;

            $config = $this->loadManifest($manifest);
            $config = $this->processManifest($config);
            $manifestHash = md5_file($manifest->getRealPath());

            $installed += (int) $this->persistTheme($themeName, $config, $manifestHash);
        }

        $deleted = $this->connection->executeQuery(
            'DELETE FROM tl_theme WHERE alias IS NOT NULL AND alias NOT IN (:aliases)',
            ['aliases' => $aliases],
            ['aliases' => Connection::PARAM_STR_ARRAY]
        )->rowCount();

        $this->filesystemLoaderWarmer->refresh();

        return new MigrationResult(true, sprintf('%d themes installed. %d themes deleted.', $installed, $deleted));
    }

    private function persistTheme(string $themeName, array $config, string $manifestHash): bool
    {
        $row = $this->connection
                ->executeQuery('SELECT id, manifestHash FROM tl_theme WHERE alias=:alias', ['alias' => $themeName])
                ->fetch(FetchMode::ASSOCIATIVE);

        // Prevent array-access error when theme not found
        $row = false === $row ? [] : $row;

        if ($manifestHash === ($row['manifestHash'] ?? '')) {
            return false;
        }

        $themeId = $row['id'] ?? null;

        $data = [
            'name' => $config['theme']['name'],
            'alias' => $themeName,
            'tstamp' => time(),
            'templates' => sprintf('themes/%s/templates', $themeName),
            'manifestHash' => $manifestHash,
        ];

        if (null === $themeId) {
            $this->connection->insert('tl_theme', $data);
            $themeId = $this->connection->lastInsertId();
        } else {
            $this->connection->update('tl_theme', $data, ['id' => $themeId]);
        }

        $layouts = $config['layouts'] ?? [];

        foreach ($layouts as $layoutName => $layout) {
            $layoutId = $this->connection
                    ->executeQuery('SELECT id FROM tl_layout WHERE pid=:pid AND alias=:alias', ['pid' => $themeId, 'alias' => $layoutName])
                    ->fetch(FetchMode::NUMERIC)[0] ?? null;

            $data = array_merge(['framework' => ''], $layout);
            $data = array_merge($data, ['alias' => $layoutName, 'pid' => $themeId, 'tstamp' => time()]);

            if (null === $layoutId) {
                // For new layouts, enable the article module in the main column
                $data = array_merge(['modules' => serialize([['mod' => '0', 'col' => 'main', 'enable' => '1']])], $data);

                $this->connection->insert('tl_layout', $data);
            } else {
                $this->connection->update('tl_layout', $data, ['id' => $layoutId]);
            }
        }

        $this->connection->executeQuery(
            'DELETE FROM tl_layout WHERE pid=:pid AND alias NOT IN (:aliases)',
            ['pid' => $themeId, 'aliases' => array_keys($layouts)],
            ['aliases' => Connection::PARAM_STR_ARRAY]
        );

        return true;
    }

    private function loadManifest($manifest)
    {
        $loaderResolver = new LoaderResolver([new YamlLoader()]);
        $delegatingLoader = new DelegatingLoader($loaderResolver);

        return $delegatingLoader->load($manifest->getRealPath());
    }

    private function processManifest(array $config): array
    {
        $config = (new Processor())->processConfiguration(new ThemeManifestConfiguration(), [$config]);

        // Serialize arrays for the DB insert.
        foreach (array_keys($config['layouts']) as $layoutName) {
            $config['layouts'][$layoutName] =
                array_map(fn ($v) => \is_array($v) ? serialize($v) : $v, $config['layouts'][$layoutName]);
        }

        return $config;
    }
}
