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
    protected function configure()
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
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $cacheFiles = glob(STORAGE.'/cache/*.php');

        if ($cacheFiles) {
            foreach ($cacheFiles as $file) {
                unlink($file);
            }
        }

        $output->writeln('<info>Cache cleared successfully.</info>');
    }
}
