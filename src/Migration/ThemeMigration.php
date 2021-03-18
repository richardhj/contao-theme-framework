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

    private static bool $installed = false;

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

        // Check fields exist
        try {
            $this->connection->executeQuery("SELECT id FROM tl_theme WHERE alias=''");
            $this->connection->executeQuery("SELECT id FROM tl_layout WHERE alias=''");
        } catch (Exception $e) {
            return false;
        }

        return !self::$installed;
    }

    public function run(): MigrationResult
    {
        $themes = $this->findThemes();

        foreach ($themes as $themeName => $theme) {
            $themeId = $this->connection
                           ->executeQuery('SELECT id FROM tl_theme WHERE alias=:alias', ['alias' => $themeName])
                           ->fetch(FetchMode::NUMERIC)[0] ?? null;

            $data = [
                'name' => $theme['name'],
                'alias' => $themeName,
                'tstamp' => time(),
                'templates' => sprintf('themes/%s/templates', $themeName),
            ];

            if (null === $themeId) {
                $this->connection->insert('tl_theme', $data);
                $themeId = $this->connection->lastInsertId();
            } else {
                $this->connection->update('tl_theme', $data, ['id' => $themeId]);
            }

            $layouts = $theme['layouts'] ?? [];

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
        }

        $this->connection->executeQuery(
            'DELETE FROM tl_theme WHERE alias IS NOT NULL AND alias NOT IN (:aliases)',
            ['aliases' => array_keys($themes)],
            ['aliases' => Connection::PARAM_STR_ARRAY]
        );

        self::$installed = true;

        return new MigrationResult(true, sprintf('%d themes installed', \count($themes)));
    }

    private function findThemes(): array
    {
        $themes = [];

        $manifests = (new Finder())
            ->files()
            ->in($this->rootDir.'/themes')
            ->name('theme.yml')
            ->getIterator()
        ;

        foreach ($manifests as $manifest) {
            $config = Yaml::parse($manifest->getContents());

            $themes[$manifest->getRelativePath()] = $this->prepareTheme($config['theme'] ?? []);
        }

        return $themes;
    }

    private function prepareTheme(array $config): array
    {
        $defaultLayout = $config['layouts']['_default'] ?? [];

        foreach (array_keys($config['layouts']) as $layoutName) {
            if ('_default' === $layoutName) {
                continue;
            }

            $config['layouts'][$layoutName] = array_merge_recursive($defaultLayout, $config['layouts'][$layoutName]);
        }

        return $config;
    }
}
