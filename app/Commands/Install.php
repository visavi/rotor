<?php

namespace App\Commands;

use Phinx\Console\Command\AbstractCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class Install extends AbstractCommand
{
    /**
     * {@inheritdoc}
     */
    protected function configure()
    {
        parent::configure();

        $this->setName('install')
             ->setDescription('Setting permissions on folders');
    }

    /**
     * Setting permissions on folders
     *
     * @param InputInterface $input
     * @param OutputInterface $output
     * @return void
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $storage = glob(RESOURCES.'/storage/*', GLOB_ONLYDIR);
        $uploads = glob(HOME.'/uploads/*', GLOB_ONLYDIR);

        $dirs = array_merge($storage, $uploads);

        foreach ($dirs as $dir) {
            $old = umask(0);
            chmod ($dir, 0777);
            umask($old);
        }

        $output->writeln('<info>setting permission success!</info>');
    }
}
