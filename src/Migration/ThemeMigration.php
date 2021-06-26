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
use Doctrine\DBAL\Connection;
use Doctrine\DBAL\Exception;
use Doctrine\DBAL\FetchMode;
use Symfony\Component\Finder\Finder;
use Symfony\Component\Yaml\Yaml;

/**
 * Persists the themes defined in `themes/foobar/theme.yml` in the database.
 */
class ThemeMigration implements MigrationInterface
{
    private Connection $connection;
    private string $rootDir;

    public function __construct(Connection $connection, string $rootDir)
    {
        $this->connection = $connection;
        $this->rootDir = $rootDir;
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
            ->name('theme.yml')
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
            ->name('theme.yml')
            ->getIterator()
        ;

        $installed = 0;
        $aliases = [];
        foreach ($manifests as $manifest) {
            $aliases[] = $manifest->getRelativePath();

            $themeName = $manifest->getRelativePath();
            $config = Yaml::parse($manifest->getContents());
            $config = $this->prepareTheme($config['theme'] ?? []);
            $manifestHash = md5_file($manifest->getRealPath());

            $installed += (int) $this->persistTheme($themeName, $config, $manifestHash);
        }

        $deleted = $this->connection->executeQuery(
            'DELETE FROM tl_theme WHERE alias IS NOT NULL AND alias NOT IN (:aliases)',
            ['aliases' => $aliases],
            ['aliases' => Connection::PARAM_STR_ARRAY]
        )->rowCount();

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
            'name' => $config['name'],
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
            $data = array_merge($layout, ['alias' => $layoutName, 'pid' => $themeId, 'tstamp' => time()]);

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

    private function prepareTheme(array $config): array
    {
        $defaultLayout = $config['layouts']['_default'] ?? [];

        foreach (array_keys($config['layouts']) as $layoutName) {
            if ('_default' !== $layoutName) {
                $config['layouts'][$layoutName] = array_merge($defaultLayout, $config['layouts'][$layoutName]);
            }

            $config['layouts'][$layoutName] =
                array_map(fn ($v) => \is_array($v) ? serialize($v) : $v, $config['layouts'][$layoutName]);
        }

        return $config;
    }
}
