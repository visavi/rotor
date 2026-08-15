<?php

namespace App\Providers;

use App\Support\Restatement;
use Carbon\CarbonImmutable;
use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Http\Request;
use Illuminate\Pagination\Paginator;
use Illuminate\Support\Facades\Blade;
use Illuminate\Support\Facades\Date;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Facades\Route;
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
        if (! defined('CURL_SSLVERSION_TLSv1_2')) {
            define('CURL_SSLVERSION_TLSv1_2', 6);
        }

        if (! defined('CURL_SSLVERSION_TLSv1_3')) {
            define('CURL_SSLVERSION_TLSv1_3', 7);
        }
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        Restatement::boot();

        $this->configureRateLimiting();

        Route::pattern('id', '\d+');
        Route::pattern('cid', '\d+');
        Route::pattern('fid', '\d+');
        Route::pattern('slug', '[a-z0-9-\.]+');
        Route::pattern('login', '[\w\-]+');

        DB::connection()->enableQueryLog();

        Paginator::$defaultView = 'app/_paginator';
        Paginator::$defaultSimpleView = 'app/_simple_paginator';

        if (setting('app_installed')) {
            $this->loadMigrationsFrom([database_path('upgrades')]);
        }

        // Only old database
        Schema::defaultStringLength(191);

        // Immutable date
        Date::use(CarbonImmutable::class);

        // Hook directive
        Blade::directive('hook', static function ($expression) {
            $args = explode(',', $expression, 2);

            $hookName = trim($args[0]);
            $args = isset($args[1]) ? trim($args[1]) : 'null';

            return "<?= \\App\\Support\\Hook::call($hookName, $args); ?>";
        });

        // Translation directive
        Blade::directive('translation', static function () {
            return '<?= translationScript(); ?>';
        });

        /*if (app()->environment('production')) {
            URL::forceScheme('https');
        }*/
    }

    /**
     * Лимиты запросов к API (web-запросы ограничивает CheckThrottle)
     */
    private function configureRateLimiting(): void
    {
        // Общий лимит на все методы api
        RateLimiter::for('api', static fn (Request $request) => Limit::perMinute(120)
            ->by($request->user()?->id ?: getIp()));

        // Авторизация — по ip, чтобы не перебирали пароли
        RateLimiter::for('api-auth', static fn () => Limit::perMinute(10)->by(getIp()));

        // If the public directory is renamed to public_html
        /*$this->app->bind('path.public', function () {
            return base_path('public_html');
        });*/
    }
}
