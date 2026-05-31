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

        foreach ($modules as $module => $data) {
            $base = base_path('modules/' . $module);

            // чтобы не падать на include отсутствующих файлов из устаревшего кэша
            if (! is_dir($base)) {
                continue;
            }

            $files = $data['files'] ?? [];
            $moduleKey = Str::snake($module);

            if ($files['views'] ?? false) {
                $this->loadViewsFrom($base . '/resources/views', $moduleKey);
            }

            if ($files['lang'] ?? false) {
                $this->loadTranslationsFrom($base . '/resources/lang', $moduleKey);
            }

            if ($files['helpers'] ?? false) {
                include_once $base . '/helpers.php';
            }

            if ($files['hooks'] ?? false) {
                include_once $base . '/hooks.php';
            }

            if ($files['routes'] ?? false) {
                $this->loadRoutesFrom($base . '/routes.php');
            }

            if ($data['config'] ?? false) {
                Config::set($moduleKey, $data['config']);
            }

            if ($files['middleware'] ?? false) {
                $middleware = include $base . '/middleware.php';

                foreach ($middleware['aliases'] ?? [] as $alias => $class) {
                    $router->aliasMiddleware($alias, $class);
                }

                foreach ($middleware['web'] ?? [] as $class) {
                    $router->pushMiddlewareToGroup('web', $class);
                }
            }

            if ($files['module'] ?? false) {
                $moduleConfig = include $base . '/module.php';

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
                if (isset($moduleConfig['schedule']) && $this->app->runningInConsole()) {
                    $this->app->booted(function () use ($moduleConfig) {
                        $moduleConfig['schedule']($this->app->make(Schedule::class));
                    });
                }

                // Регистрация пересчётов
                foreach ($moduleConfig['restatement'] ?? [] as $key => $callback) {
                    Restatement::register($key, $callback);
                }
            }

            if (! empty($files['commands']) && $this->app->runningInConsole()) {
                $this->commands($files['commands']);
            }
        }
    }
}
