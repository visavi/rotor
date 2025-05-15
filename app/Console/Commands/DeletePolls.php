<?php

namespace App\Console\Commands;

use App\Models\Poll;
use Illuminate\Console\Command;
use Symfony\Component\Console\Command\Command as SymfonyCommand;

class DeletePolls extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'delete:polls';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Delete polls';

    /**
     * Удаляет старые записи голосов
     */
    public function handle(): int
    {
        Poll::query()
            ->where('created_at', '<', strtotime('-1 year', SITETIME))
            ->delete();

        $this->info('Polls successfully deleted.');

        return SymfonyCommand::SUCCESS;
    }
}
