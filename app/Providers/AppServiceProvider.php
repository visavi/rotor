<?php

namespace App\Providers;

use Illuminate\Pagination\Paginator;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     *
     * @return void
     */
    public function register()
    {
        //
    }

    /**
     * Bootstrap any application services.
     *
     * @return void
     */
    public function boot()
    {
        DB::connection()->enableQueryLog();

        Paginator::$defaultView = 'app/_paginator';
        Paginator::$defaultSimpleView = 'app/_simple_paginator';

        if (setting('app_installed')) {
            $this->loadMigrationsFrom([database_path('upgrades')]);
        }

        // If the public directory is renamed to public_html
        /*$this->app->bind('path.public', function () {
            return base_path('public_html');
        });*/
    }
}
