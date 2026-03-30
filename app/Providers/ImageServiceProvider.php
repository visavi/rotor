<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Intervention\Image\ImageManager;

class ImageServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     */
    public function register(): void
    {
        $this->app->singleton(ImageManager::class, static function () {
            return new ImageManager(
                driver: config('image.driver'),
                autoOrientation: config('image.options.autoOrientation', true),
                decodeAnimation: config('image.options.decodeAnimation', true),
                backgroundColor: config('image.options.backgroundColor', 'ffffff'),
                strip: config('image.options.strip', false)
            );
        });
    }

    /**
     * Bootstrap services.
     */
    public function boot(): void
    {
        //
    }
}
