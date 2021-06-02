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
     * @var Filesystem
     */
    private $filesystem;

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct(Filesystem $filesystem)
    {
        parent::__construct();

        $this->filesystem = $filesystem;
    }

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        $storage = glob(storage_path('/*'), GLOB_ONLYDIR);
        $uploads = glob(public_path('uploads/*'), GLOB_ONLYDIR);
        $modules = [public_path('assets/modules')];

        $dirs = array_merge($storage, $uploads, $modules);

        $this->filesystem->chmod($dirs, 0777);

        $this->info('Permissions set successfully.');

        return 0;
    }
}
