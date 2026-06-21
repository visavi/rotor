<?php

namespace App\Console\Commands;

use App\Models\Module;
use Illuminate\Console\Command;
use Symfony\Component\Console\Command\Command as SymfonyCommand;

class ModuleSync extends Command
{
    /**
     * The name and signature of the console command.
     */
    protected $signature = 'module:sync';

    /**
     * The console command description.
     */
    protected $description = 'Sync active modules with the core: symlinks, published files and cache';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        // Обновление ядра затирает опубликованные файлы — публикуем заново.
        // syncAll изолирует битые модули и сам сбрасывает кэш скана модулей.
        $failed = Module::syncAll();

        foreach ($failed as $name => $message) {
            $this->warn(sprintf('Module "%s" failed: %s', $name, $message));
        }

        $this->info('Modules links created and files published.');

        return SymfonyCommand::SUCCESS;
    }
}
