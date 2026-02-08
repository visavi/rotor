<?php

namespace App\Console\Commands;

use App\Models\Module;
use Illuminate\Console\Command;
use Symfony\Component\Console\Command\Command as SymfonyCommand;

class ModuleLink extends Command
{
    /**
     * The name and signature of the console command.
     */
    protected $signature = 'module:link';

    /**
     * The console command description.
     */
    protected $description = 'Create a modules links';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $modules = Module::query()->where('active', true)->get();

        foreach ($modules as $module) {
            $module->createSymlink();
        }

        $this->info('Modules links created.');

        return SymfonyCommand::SUCCESS;
    }
}
