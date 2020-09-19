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
     *
     * @return int
     */
    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        if (file_exists(STORAGE . '/caches/config.php')) {
            unlink(STORAGE . '/caches/config.php');
        }

        $output->writeln('<info>Config cleared successfully.</info>');

        return 0;
    }
}
