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

use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Filesystem\Filesystem;

#[AsCommand(name: 'contao:theme:create', description: 'The theme name and directory name')]
class CreateThemeCommand extends Command
{
    private Filesystem $filesystem;
    private string $rootDir;

    public function __construct(Filesystem $filesystem, string $rootDir)
    {
        parent::__construct();

        $this->filesystem = $filesystem;
        $this->rootDir = $rootDir;
    }

    protected function configure(): void
    {
        $this->addArgument('name', InputArgument::REQUIRED, 'The theme name');
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
