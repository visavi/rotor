<?php

namespace App\Commands;

use Phinx\Console\Command\AbstractCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class AppInstall extends AbstractCommand
{
    /**
     * {@inheritdoc}
     */
    protected function configure(): void
    {
        parent::configure();

        $this->setName('app:install')
             ->setDescription('Setting permissions on folders');
    }

    /**
     * Setting permissions on folders
     *
     * @param InputInterface $input
     * @param OutputInterface $output
     * @return void
     */
    protected function execute(InputInterface $input, OutputInterface $output): void
    {
        $storage = glob(STORAGE.'/*', GLOB_ONLYDIR);
        $uploads = glob(UPLOADS.'/*', GLOB_ONLYDIR);
        $modules = [HOME . '/assets/modules'];

        $dirs = array_merge($storage, $uploads, $modules);

        foreach ($dirs as $dir) {
            $old = umask(0);
            chmod($dir, 0777);
            umask($old);
        }

        $output->writeln('<info>Setting permission successfully.</info>');
    }
}
