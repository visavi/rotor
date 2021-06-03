<?php

namespace App\Console\Commands;

use App\Models\Reader;
use Illuminate\Console\Command;

class DeleteReaders extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'delete:readers';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Delete readers';

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
     * Удаляет старые записи статистики просмотров и скачиваний
     *
     * @return int
     */
    public function handle()
    {
        Reader::query()
            ->where('created_at', '<', strtotime('-6 month', SITETIME))
            ->delete();

        $this->info('Readers successfully deleted.');

        return 0;
    }
}
