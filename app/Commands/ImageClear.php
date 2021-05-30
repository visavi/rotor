<?php

declare(strict_types=1);

namespace App\Commands;

use Phinx\Console\Command\AbstractCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class ImageClear extends AbstractCommand
{
    /**
     * {@inheritdoc}
     */
    protected function configure(): void
    {
        parent::configure();

        $this->setName('image:clear')
             ->setDescription('Flush the application image thumbnails');
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
        $images = glob(public_path('uploads/thumbnails/*.{gif,png,jpg,jpeg}'), GLOB_BRACE);

        if ($images) {
            foreach ($images as $image) {
                unlink($image);
            }
        }

        $output->writeln('<info>Image cleared successfully.</info>');

        return 0;
    }
}
