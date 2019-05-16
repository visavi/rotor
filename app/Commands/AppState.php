<?php

declare(strict_types=1);

namespace App\Commands;

use Phinx\Console\Command\AbstractCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class AppState extends AbstractCommand
{
    /**
     * {@inheritdoc}
     */
    protected function configure(): void
    {
        parent::configure();

        $this->setName('app:state')
             ->setDescription('Change application state');
    }

    /**
     * Write APP_NEW false
     *
     * @param InputInterface $input
     * @param OutputInterface $output
     * @return void
     */
    protected function execute(InputInterface $input, OutputInterface $output): void
    {
        file_put_contents(BASEDIR . '/.env', str_replace(
            'APP_NEW=true',
            'APP_NEW=false',
            file_get_contents(BASEDIR . '/.env')
        ));

        $output->writeln('<info>Application state changed successfully.</info>');
    }
}
