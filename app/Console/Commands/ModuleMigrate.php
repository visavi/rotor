<?php

namespace App\Console\Commands;

use App\Models\Module;
use Illuminate\Console\Command;
use Symfony\Component\Console\Command\Command as SymfonyCommand;

class ModuleMigrate extends Command
{
    /**
     * The name and signature of the console command.
     */
    protected $signature = 'module:migrate';

    /**
     * The console command description.
     */
    protected $description = 'Run pending migrations of active modules';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        // artisan migrate миграций модулей не видит, а версия модуля при деплое
        // не меняется — без этого новые миграции ждали бы кнопки в админке.
        // Выключенные пропускаем: включение из админки мигрирует их само
        foreach (Module::query()->where('active', true)->get() as $module) {
            $module->migrate();

            $this->info(sprintf('Module "%s" migrated.', $module->name));
        }

        // Миграции модулей добавляют настройки, а кэш их не видит:
        // без сброса setting() отдавал бы null до сохранения настроек в админке
        clearCache('settings');

        return SymfonyCommand::SUCCESS;
    }
}
