<?php

namespace App\Console\Commands;

use App\Models\Error;
use App\Models\Log;
use Illuminate\Console\Command;

class DeleteLogs extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'delete:logs';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Delete logs';

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
     * Удаляет старые записи логов
     *
     * @return int
     */
    public function handle()
    {
        $time = strtotime('-1 month', SITETIME);

        Error::query()
            ->where('created_at', '<', $time)
            ->delete();

        Log::query()
            ->where('created_at', '<', $time)
            ->delete();

        $this->info('Logs successfully deleted.');

        return 0;
    }
}
