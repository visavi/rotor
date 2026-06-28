<?php

namespace App\Console\Commands;

use App\Models\Error;
use App\Models\Log;
use Illuminate\Console\Command;
use Symfony\Component\Console\Command\Command as SymfonyCommand;

class DeleteLogs extends Command
{
    /**
     * The name and signature of the console command.
     */
    protected $signature = 'delete:logs';

    /**
     * The console command description.
     */
    protected $description = 'Delete logs';

    /**
     * Удаляет старые записи логов
     */
    public function handle(): int
    {
        Error::query()
            ->where('created_at', '<', now()->subMonth())
            ->delete();

        Log::query()
            ->where('created_at', '<', now()->subMonth())
            ->delete();

        $this->info('Logs successfully deleted.');

        return SymfonyCommand::SUCCESS;
    }
}
