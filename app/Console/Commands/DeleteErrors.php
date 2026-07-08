<?php

namespace App\Console\Commands;

use App\Models\Error;
use Illuminate\Console\Command;
use Symfony\Component\Console\Command\Command as SymfonyCommand;

class DeleteErrors extends Command
{
    /**
     * The name and signature of the console command.
     */
    protected $signature = 'delete:errors';

    /**
     * The console command description.
     */
    protected $description = 'Delete error logs';

    /**
     * Удаляет старые записи логов ошибок
     */
    public function handle(): int
    {
        Error::query()
            ->where('created_at', '<', now()->subMonth())
            ->delete();

        $this->info('Errors successfully deleted.');

        return SymfonyCommand::SUCCESS;
    }
}
