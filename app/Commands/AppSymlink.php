<?php

declare(strict_types=1);

namespace App\Commands;

use Phinx\Console\Command\AbstractCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Filesystem\Filesystem;

class AppSymlink extends AbstractCommand
{
    /**
     * {@inheritdoc}
     */
    protected function configure(): void
    {
        parent::configure();

        $this->setName('app:symlink')
             ->setDescription('Create file symlinks');
    }

    /**
     * Create symlinks
     *
     * @param InputInterface $input
     * @param OutputInterface $output
     *
     * @return void
     */
    protected function execute(InputInterface $input, OutputInterface $output): void
    {
        $filesystem = new Filesystem();
        $languages = array_map('basename', glob(RESOURCES . '/lang/*', GLOB_ONLYDIR));

        foreach ($languages as $language) {
            $languagePath = RESOURCES . '/lang/' . $language;
            $assetsPath  = HOME . '/assets/modules/';

            $relativePath = $filesystem->makePathRelative($languagePath, $assetsPath);

            $filesystem->symlink(
                $relativePath . 'main.js',
                $assetsPath . $language . '.js',
                true
            );
        }

        $output->writeln('<info>Symlinks successfully created.</info>');
    }
}
