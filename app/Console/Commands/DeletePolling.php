<?php

namespace App\Console\Commands;

use App\Models\Polling;
use Illuminate\Console\Command;
use Symfony\Component\Console\Command\Command as SymfonyCommand;

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
    protected $description = 'Delete polling';

    /**
     * Удаляет старые записи голосов
     */
    public function handle(): int
    {
        Polling::query()
            ->where('created_at', '<', strtotime('-1 year', SITETIME))
            ->delete();

        $this->info('Polling successfully deleted.');

        return SymfonyCommand::SUCCESS;
    }
}
