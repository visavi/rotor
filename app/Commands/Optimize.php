<?php

declare(strict_types=1);

namespace App\Commands;

use Phinx\Console\Command\AbstractCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class Optimize extends AbstractCommand
{
    /**
     * {@inheritdoc}
     */
    protected function configure(): void
    {
        parent::configure();

        $this->setName('optimize')
             ->setDescription('Flush all cache');
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
        runCommand(new CacheClear());
        runCommand(new RouteClear());
        runCommand(new ConfigClear());
        runCommand(new ViewClear());

        $output->writeln('<info>Cache cleared successfully.</info>');

        return 0;
    }
}
