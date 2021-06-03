<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;

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
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Пересчитывает счетчик объявлений
     *
     * @return int
     */
    public function handle()
    {
        restatement('boards');

        $this->info('Board restatement successfully.');

        return 0;
    }
}
