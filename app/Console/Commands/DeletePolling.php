<?php

namespace App\Console\Commands;

use App\Models\Polling;
use Illuminate\Console\Command;

class DeletePolling extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'delete:polling';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Delete pending user';

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
     * Удаляет старые записи голосов
     *
     * @return int
     */
    public function handle()
    {
        Polling::query()
            ->where('created_at', '<', strtotime('-1 year', SITETIME))
            ->delete();

        $this->info('Polling successfully deleted.');

        return 0;
    }
}
