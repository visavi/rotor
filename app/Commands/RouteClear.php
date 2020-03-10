<?php

declare(strict_types=1);

namespace App\Commands;

use Phinx\Console\Command\AbstractCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class RouteClear extends AbstractCommand
{
    /**
     * {@inheritdoc}
     */
    protected function configure(): void
    {
        parent::configure();

        $this->setName('route:clear')
             ->setDescription('Flush the routes cache');
    }

    /**
     * Route cleared
     *
     * @param InputInterface $input
     * @param OutputInterface $output
     *
     * @return int
     */
    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        if (file_exists(STORAGE . '/caches/routes.php')) {
            unlink (STORAGE . '/caches/routes.php');
        }

        $output->writeln('<info>Routes cleared successfully.</info>');

        return 0;
    }
}
