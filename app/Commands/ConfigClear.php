<?php

declare(strict_types=1);

namespace App\Commands;

use Phinx\Console\Command\AbstractCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class ConfigClear extends AbstractCommand
{
    /**
     * {@inheritdoc}
     */
    protected function configure(): void
    {
        parent::configure();

        $this->setName('config:clear')
             ->setDescription('Flush the config cache');
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
        if (file_exists(STORAGE . '/temp/config.dat')) {
            unlink (STORAGE . '/temp/config.dat');
        }


        $output->writeln('<info>Config cleared successfully.</info>');
    }
}
