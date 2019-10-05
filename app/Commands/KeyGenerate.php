<?php

declare(strict_types=1);

namespace App\Commands;

use Illuminate\Support\Str;
use Phinx\Console\Command\AbstractCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class KeyGenerate extends AbstractCommand
{
    /**
     * {@inheritdoc}
     */
    protected function configure(): void
    {
        parent::configure();

        $this->setName('key:generate')
             ->setDescription('Set the application key');
    }

    /**
     * Setting application key
     *
     * @param InputInterface $input
     * @param OutputInterface $output
     * @return void
     */
    protected function execute(InputInterface $input, OutputInterface $output): void
    {
        $key = Str::random(32);
        $this->writeNewEnvironmentFileWith($key);

        $output->writeln('<info>Application key ['.$key.'] set successfully.</info>');
    }

    /**
     * Write a new environment file with the given key.
     *
     * @param  string  $key
     * @return void
     */
    protected function writeNewEnvironmentFileWith($key): void
    {
        file_put_contents(BASEDIR . '/.env', preg_replace(
            $this->keyReplacementPattern(),
            'APP_KEY=' . $key,
            file_get_contents(BASEDIR . '/.env')
        ));
    }

    /**
     * Get a regex pattern that will match env APP_KEY with any random key.
     *
     * @return string
     */
    protected function keyReplacementPattern(): string
    {
        $escaped = preg_quote('=' . config('APP_KEY'), '/');
        return "/^APP_KEY{$escaped}/m";
    }
}
