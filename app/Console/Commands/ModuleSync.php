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
        $modules = Module::query()->where('active', true)->get();

        foreach ($modules as $module) {
            $module->createSymlink();

            // Обновление ядра затирает опубликованные файлы — публикуем заново
            $module->publish();
        }

        // Сбрасываем кэш скана модулей, чтобы новый релиз подхватил
        // изменения в структуре (views, lang, routes, hooks и т.д.)
        clearCache(['modules', 'settings']);

        $this->info('Modules links created and files published.');

        return SymfonyCommand::SUCCESS;
    }
}
