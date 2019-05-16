<?php

declare(strict_types=1);

namespace App\Commands;

use Phinx\Console\Command\AbstractCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class AppConfigure extends AbstractCommand
{
    /**
     * {@inheritdoc}
     */
    protected function configure(): void
    {
        parent::configure();

        $this->setName('app:configure')
             ->setDescription('Configures the application');
    }

    /**
     * Configures the app
     *
     * @param InputInterface $input
     * @param OutputInterface $output
     * @return void
     */
    protected function execute(InputInterface $input, OutputInterface $output): void
    {
        $this->setPermissions();
        $this->createSymlinks();

        $output->writeln('<info>Application successfully configured.</info>');
    }

    /**
     * Set permissions
     *
     * @return void
     */
    protected function setPermissions(): void
    {
        $storage = glob(STORAGE . '/*', GLOB_ONLYDIR);
        $uploads = glob(UPLOADS . '/*', GLOB_ONLYDIR);
        $modules = [HOME . '/assets/modules'];

        $dirs = array_merge($storage, $uploads, $modules);

        foreach ($dirs as $dir) {
            $old = umask(0);
            @chmod($dir, 0777);
            umask($old);
        }
    }

    /**
     * Create symlinks
     *
     * @return void
     */
    protected function createSymlinks(): void
    {
        $languages = array_map('basename', glob(RESOURCES . '/lang/*', GLOB_ONLYDIR));

        foreach ($languages as $language) {
            $file = RESOURCES . '/lang/' . $language . '/main.js';
            $link = HOME . '/assets/modules/' . $language . '.js';

            deleteFile($link, false);

            if (file_exists($file)) {
                @symlink($file, $link);
            }
        }
    }
}
