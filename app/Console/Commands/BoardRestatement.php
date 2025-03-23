<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Symfony\Component\Console\Command\Command as SymfonyCommand;

class BoardRestatement extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'board:restatement';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Board restatement';

    /**
     * Пересчитывает счетчик объявлений
     *
     * @return int
     */
    public function handle(): int
    {
        restatement('boards');

        $this->info('Board restatement successfully.');

        return SymfonyCommand::SUCCESS;;
    }
}
