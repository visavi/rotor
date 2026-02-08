<?php

namespace App\Console\Commands;

use App\Models\Dialogue;
use Illuminate\Console\Command;
use Symfony\Component\Console\Command\Command as SymfonyCommand;

class DeleteDialogues extends Command
{
    /**
     * The name and signature of the console command.
     */
    protected $signature = 'delete:dialogues';

    /**
     * The console command description.
     */
    protected $description = 'Delete old dialogues';

    /**
     * Удаляет старые диалоги с пользователем Система
     */
    public function handle(): int
    {
        $dialogues = Dialogue::query()
            ->where('created_at', '<', strtotime('-3 year', SITETIME))
            ->where('author_id', 0)
            ->limit(1000)
            ->get();

        $dialogues->each(function (Dialogue $dialogue) {
            $dialogue->message->delete();
            $dialogue->delete();
        });

        $this->info('Dialogues successfully deleted.');

        return SymfonyCommand::SUCCESS;
    }
}
