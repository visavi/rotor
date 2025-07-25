<?php

namespace App\Providers;

use Carbon\CarbonImmutable;
use Illuminate\Pagination\Paginator;
use Illuminate\Support\Facades\Blade;
use Illuminate\Support\Facades\Date;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\URL;
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

        // Immutable date
        Date::use(CarbonImmutable::class);

        Blade::directive('hook', function ($expression) {
            $args = explode(',', $expression, 2);

            $hookName = trim($args[0]);
            $args = isset($args[1]) ? trim($args[1]) : 'null';

            return "<?= \\App\\Classes\\Hook::call($hookName, $args); ?>";
        });

        /*if (app()->environment('production')) {
            URL::forceScheme('https');
        }*/

        // If the public directory is renamed to public_html
        /*$this->app->bind('path.public', function () {
            return base_path('public_html');
        });*/
    }
}
