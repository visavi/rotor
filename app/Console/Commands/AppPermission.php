<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Symfony\Component\Filesystem\Filesystem;

class AppPermission extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:permission';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Set file permissions';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle(Filesystem $filesystem)
    {
        $storage = glob(storage_path('{*,*/*,*/*/*}'), GLOB_BRACE | GLOB_ONLYDIR);
        $uploads = glob(public_path('uploads/*'), GLOB_ONLYDIR);
        $dirs    = [public_path('assets/modules'), base_path('bootstrap/cache')];

        $dirs = array_merge($storage, $uploads, $dirs);

        $filesystem->chmod($dirs, 0777);

        $this->info('Permissions set successfully.');

        return 0;
    }
}
