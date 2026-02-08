<?php

namespace App\Console\Commands;

use App\Models\File;
use Illuminate\Console\Command;
use Symfony\Component\Console\Command\Command as SymfonyCommand;

class DeleteFiles extends Command
{
    /**
     * The name and signature of the console command.
     */
    protected $signature = 'delete:files';

    /**
     * The console command description.
     */
    protected $description = 'Delete unattached files';

    /**
     * Удаляет не прикрепленные файлы
     */
    public function handle(): int
    {
        $files = File::query()
            ->where('relate_id', 0)
            ->where('created_at', '<', strtotime('-1 day', SITETIME))
            ->get();

        foreach ($files as $file) {
            $file->delete();
        }

        $this->info('Files successfully deleted.');

        return SymfonyCommand::SUCCESS;
    }
}
