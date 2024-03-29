<?php

namespace App\Providers;

use Illuminate\Pagination\Paginator;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        DB::connection()->enableQueryLog();

        Paginator::$defaultView = 'app/_paginator';
        Paginator::$defaultSimpleView = 'app/_simple_paginator';

        if (setting('app_installed')) {
            $this->loadMigrationsFrom([database_path('upgrades')]);
        }

        Schema::defaultStringLength(191);

        // If the public directory is renamed to public_html
        /*$this->app->bind('path.public', function () {
            return base_path('public_html');
        });*/
    }
}
