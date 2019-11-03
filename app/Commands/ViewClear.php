<?php

declare(strict_types=1);

namespace App\Commands;

use Phinx\Console\Command\AbstractCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class ViewClear extends AbstractCommand
{
    /**
     * {@inheritdoc}
     */
    protected function configure(): void
    {
        parent::configure();

        $this->setName('view:clear')
             ->setDescription('Flush the view cache');
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
        $cacheFiles = glob(STORAGE . '/views/*.php');

        if ($cacheFiles) {
            foreach ($cacheFiles as $file) {
                unlink($file);
            }
        }

        $output->writeln('<info>View cleared successfully.</info>');
    }
}
