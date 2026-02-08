<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Filesystem\Filesystem;
use Symfony\Component\Console\Command\Command as SymfonyCommand;

class AppPermission extends Command
{
    /**
     * The name and signature of the console command.
     */
    protected $signature = 'app:permission';

    /**
     * The console command description.
     */
    protected $description = 'Set file permissions';

    /**
     * Execute the console command.
     */
    public function handle(Filesystem $filesystem): int
    {
        $storage = glob(storage_path('{*,*/*,*/*/*}'), GLOB_BRACE | GLOB_ONLYDIR);
        $uploads = glob(public_path('uploads/*'), GLOB_ONLYDIR);
        $dirs = [public_path('assets/modules'), base_path('bootstrap/cache')];

        $dirs = array_merge($storage, $uploads, $dirs);

        foreach ($dirs as $dir) {
            $filesystem->chmod($dir, 0777);
        }

        $this->info('Permissions set successfully.');

        return SymfonyCommand::SUCCESS;
    }
}
