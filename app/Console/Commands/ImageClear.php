<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;

class ImageClear extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'image:clear';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Flush the application image thumbnails';

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
    public function handle()
    {
        $images = glob(public_path('uploads/thumbnails/*.{gif,png,jpg,jpeg,webp}'), GLOB_BRACE);

        if ($images) {
            foreach ($images as $image) {
                unlink($image);
            }
        }

        $this->info('Image cleared successfully.');

        return 0;
    }
}
