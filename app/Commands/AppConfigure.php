<?php

declare(strict_types=1);

namespace App\Commands;

use Exception;
use Phinx\Console\Command\AbstractCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\NullOutput;
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
     * Set permissions
     *
     * @param  InputInterface  $input
     * @param  OutputInterface  $output
     *
     * @return int
     * @throws Exception
     */
    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $command = $this->getApplication()->find('app:permission');
        $command->run($input, new NullOutput());

        $command = $this->getApplication()->find('app:symlink');
        $command->run($input, new NullOutput());

        $output->writeln('<info>Application successfully configured.</info>');

        return 0;
    }
}
