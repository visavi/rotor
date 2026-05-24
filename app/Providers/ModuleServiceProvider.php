<?php

namespace App\Providers;

use App\Classes\Registry;
use App\Classes\Restatement;
use App\Models\Module;
use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Database\Eloquent\Relations\Relation;
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

                foreach ($middleware['aliases'] ?? [] as $alias => $class) {
                    $router->aliasMiddleware($alias, $class);
                }

                foreach ($middleware['web'] ?? [] as $class) {
                    $router->pushMiddlewareToGroup('web', $class);
                }
            }

            // Загрузка capabilities из module.php
            $moduleFile = base_path('modules/' . $module . '/module.php');
            if (file_exists($moduleFile)) {
                $moduleConfig = include $moduleFile;

                // Регистрация моделей и их возможностей
                foreach ($moduleConfig['models'] ?? [] as $model => $config) {
                    /** @var class-string $model */
                    $morphName = $model::$morphName;
                    Relation::morphMap([$morphName => $model]);

                    if ($search = $config['search'] ?? null) {
                        Registry::search($model, $search['view'], $search['with'] ?? []);
                    }

                    if ($feed = $config['feed'] ?? null) {
                        Registry::feed($model, $feed['withs'], $feed['view']);
                    }

                    match ($config['upload'] ?? null) {
                        'media' => Registry::mediaType($morphName),
                        'file'  => Registry::fileType($morphName),
                        default => null,
                    };

                    if (! empty($config['rating'])) {
                        Registry::ratingType($morphName);
                    }

                    if ($label = $config['label'] ?? null) {
                        Registry::label($morphName, $label);
                    }

                    if (! empty($config['spam'])) {
                        Registry::spam($morphName, Registry::$labelTypes[$morphName] ?? $morphName);
                    }
                }

                // Регистрация наблюдателей
                foreach ($moduleConfig['observers'] ?? [] as $modelClass => $observerClass) {
                    $modelClass::observe($observerClass);
                }

                // Регистрация консольных команд
                if (isset($moduleConfig['schedule'])) {
                    $this->app->booted(function () use ($moduleConfig) {
                        $moduleConfig['schedule']($this->app->make(Schedule::class));
                    });
                }

                // Регистрация пересчётов
                foreach ($moduleConfig['restatement'] ?? [] as $key => $callback) {
                    Restatement::register($key, $callback);
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
