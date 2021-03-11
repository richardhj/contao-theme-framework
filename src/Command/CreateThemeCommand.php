<?php

declare(strict_types=1);

/*
 * This file is part of richardhj/contao-theme-framework.
 *
 * (c) Richard Henkenjohann <richardhenkenjohann@googlemail.com>
 *
 * @license MIT
 */

namespace Richardhj\ContaoThemeFramework\Command;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Filesystem\Filesystem;

class CreateThemeCommand extends Command
{
    protected static $defaultName = 'contao:theme:create';
    private Filesystem $filesystem;
    private string $rootDir;

    public function __construct(Filesystem $filesystem, string $rootDir)
    {
        $this->filesystem = $filesystem;
        $this->rootDir = $rootDir;
    }

    protected function configure(): void
    {
        $this
            ->addArgument('name', InputArgument::REQUIRED, 'The theme name')
            ->setDescription('The theme name and directory name')
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $themeName = $input->getArgument('name');

        if ($this->filesystem->exists($this->rootDir.'/themes/'.$themeName)) {
            $output->writeln(sprintf('<error>Theme folder %s already exists.</error>', $themeName));

            return Command::FAILURE;
        }

        $this->filesystem->mirror('../Resources/skeleton/theme', $this->rootDir.'/themes/'.$themeName);

        return Command::SUCCESS;
    }
}
