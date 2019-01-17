<?php

namespace App\Commands;

use Phinx\Console\Command\AbstractCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class CacheClear extends AbstractCommand
{
    /**
     * {@inheritdoc}
     */
    protected function configure(): void
    {
        parent::configure();

        $this->setName('cache:clear')
             ->setDescription('Flush the application cache');
    }

    /**
     * Cache cleared
     *
     * @param InputInterface $input
     * @param OutputInterface $output
     * @return void
     */
    protected function execute(InputInterface $input, OutputInterface $output): void
    {
        $cacheFiles = glob(STORAGE.'/caches/*.php');

        if ($cacheFiles) {
            foreach ($cacheFiles as $file) {
                unlink($file);
            }
        }

        $cacheFiles = glob(STORAGE.'/temp/*.dat');
        $cacheFiles = array_diff($cacheFiles, [
            STORAGE.'/temp/checker.dat',
            STORAGE.'/temp/counter7.dat',
        ]);

        if ($cacheFiles) {
            foreach ($cacheFiles as $file) {
                unlink ($file);
            }
        }

        $output->writeln('<info>Cache cleared successfully.</info>');
    }
}
