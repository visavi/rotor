<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Symfony\Component\Console\Command\Command as SymfonyCommand;

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
     * Execute the console command.
     */
    public function handle(): int
    {
        $images = glob(public_path('uploads/thumbnails/*.{gif,png,jpg,jpeg,webp}'), GLOB_BRACE);

        if ($images) {
            foreach ($images as $image) {
                unlink($image);
            }
        }

        $this->info('Image cleared successfully.');

        return SymfonyCommand::SUCCESS;
    }
}
