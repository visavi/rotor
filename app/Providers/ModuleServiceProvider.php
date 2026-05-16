<?php

namespace App\Providers;

use App\Console\Commands\SearchImport;
use App\Models\Feed;
use App\Models\Module;
use App\Models\Search;
use Illuminate\Database\Eloquent\Relations\Relation;
use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Routing\Router;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Str;

class ModuleServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap services.
     */
    public function boot(Router $router): void
    {
        $modules = Module::getEnabledModules();

        foreach ($modules as $module => $settings) {
            $moduleKey = Str::snake($module);

            // Загрузка helpers
            $helpersFile = base_path('modules/' . $module . '/helpers.php');
            if (file_exists($helpersFile)) {
                include_once $helpersFile;
            }

            // Загрузка hooks
            $hooksFile = base_path('modules/' . $module . '/hooks.php');
            if (file_exists($hooksFile)) {
                include_once $hooksFile;
            }

            // Загрузка маршрутов
            $routesFile = base_path('modules/' . $module . '/routes.php');
            if (file_exists($routesFile)) {
                $this->loadRoutesFrom($routesFile);
            }

            // Загрузка представлений
            $viewsPath = base_path('modules/' . $module . '/resources/views');
            if (file_exists($viewsPath)) {
                $this->loadViewsFrom($viewsPath, $moduleKey);
            }

            // Загрузка языковых файлов
            $langPath = base_path('modules/' . $module . '/resources/lang');
            if (file_exists($langPath)) {
                $this->loadTranslationsFrom($langPath, $moduleKey);
            }

            // Загрузка конфигурации
            $configFile = base_path('modules/' . $module . '/config.php');
            if (file_exists($configFile)) {
                $this->mergeConfigFrom($configFile, $moduleKey);

                if ($settings) {
                    Config::set($moduleKey, array_replace_recursive(
                        config($moduleKey, []),
                        $settings
                    ));
                }
            }

            // Регистрация middleware
            $middlewareFile = base_path('modules/' . $module . '/middleware.php');
            if (file_exists($middlewareFile)) {
                $middleware = include $middlewareFile;
                foreach ($middleware as $alias => $class) {
                    $router->aliasMiddleware($alias, $class);
                    $router->pushMiddlewareToGroup('web', $class);
                }
            }

            // Загрузка capabilities из module.php
            $moduleFile = base_path('modules/' . $module . '/module.php');
            if (file_exists($moduleFile)) {
                $moduleConfig = include $moduleFile;

                // Регистрация morph map
                foreach ($moduleConfig['morphs'] ?? [] as $class) {
                    /** @var class-string $class */
                    Relation::morphMap([$class::$morphName => $class]);
                }

                // Регистрация searchable типов
                foreach ($moduleConfig['searchable'] ?? [] as $class => $label) {
                    /** @var class-string $class */
                    Search::$types[$class::$morphName] = $label;
                    SearchImport::$classes[] = $class;
                }

                // Регистрация feed типов
                foreach ($moduleConfig['feedTypes'] ?? [] as $key => $config) {
                    Feed::$types[$key] = $config;
                }

                // Регистрация feed вьюх
                foreach ($moduleConfig['feedViews'] ?? [] as $morphClass => $view) {
                    Feed::$viewMap[$morphClass] = $view;
                }

                // Регистрация search вьюх
                foreach ($moduleConfig['searchViews'] ?? [] as $morphClass => $view) {
                    Search::$viewMap[$morphClass] = $view;
                }

                // Регистрация расписания
                if (isset($moduleConfig['schedule'])) {
                    $this->app->booted(function () use ($moduleConfig) {
                        $moduleConfig['schedule']($this->app->make(Schedule::class));
                    });
                }
            }

            // Регистрация консольных команд
            if ($this->app->runningInConsole()) {
                $commands = array_map(
                    static fn ($file) => 'Modules\\' . $module . '\\Console\\' . basename($file, '.php'),
                    glob(base_path('modules/' . $module . '/Console/*.php')) ?: []
                );
                $this->commands($commands);
            }
        }
    }
}
