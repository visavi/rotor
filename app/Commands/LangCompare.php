<?php

declare(strict_types=1);

namespace App\Commands;

use Phinx\Console\Command\AbstractCommand;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class LangCompare extends AbstractCommand
{
    /**
     * {@inheritdoc}
     */
    protected function configure(): void
    {
        parent::configure();

        $this->setName('lang:compare')
            ->addArgument('lang1', InputArgument::REQUIRED, 'First lang')
            ->addArgument('lang2', InputArgument::REQUIRED, 'Second lang')
            ->setDescription('Compare lang files');
    }

    /**
     * Lang compare
     *
     * @param InputInterface $input
     * @param OutputInterface $output
     * @return void
     */
    protected function execute(InputInterface $input, OutputInterface $output): void
    {
        $lang1 = $input->getArgument('lang1');
        $lang2 = $input->getArgument('lang2');

        if (!file_exists(RESOURCES . '/lang/' . $lang1)) {
            $output->writeln('<error>Lang "' . $lang1 . '" not found</error>');
            return;
        }

        if (!file_exists(RESOURCES . '/lang/' . $lang2)) {
            $output->writeln('<error>Lang "' . $lang2 . '" not found</error>');
            return;
        }

        $langFiles = glob(RESOURCES . '/lang/' . $lang1 . '/*.php');

        foreach ($langFiles as $file) {
            $array1 = require ($file);

            $otherFile = str_replace($lang1, $lang2, $file);
            if (file_exists($otherFile)) {
                $array2 = require ($otherFile);

                $diff1 = array_diff_key($array1, $array2);

                if ($diff1) {
                    $output->writeln('<fg=blue>Not keys in file "' . $lang2 . '/' . basename($otherFile) . '"</> <comment>(' . implode(', ', array_keys($diff1)) . ')</comment>');
                }

                $diff2 = array_diff_key($array2, $array1);

                if ($diff2) {
                    $output->writeln('<fg=yellow>Extra keys in File "' . $lang1 . '/' . basename($otherFile) . '"</> <comment>(' . implode(', ', array_keys($diff2)) . ')</comment>');
                }

                if (empty($diff1) && empty($diff2)) {
                    $output->writeln('<info>File "' . $lang1 . '/' . basename($otherFile) . '" identical!</info>');
                }

            } else {
                $output->writeln('<error>File "' . $lang2 . '/' . basename($otherFile) . '" not found!</error>');
            }
        }
    }
}
