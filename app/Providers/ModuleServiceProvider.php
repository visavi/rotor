<?php

namespace App\Providers;

use App\Models\Module;
use App\Support\Registry;
use App\Support\Restatement;
use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\Relation;
use Illuminate\Routing\Router;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Str;

class ModuleServiceProvider extends ServiceProvider
{
    /**
     * Модули, которые не удалось загрузить в текущем запросе.
     * Заполняется в boot(), читается админкой для подсветки проблемы.
     *
     * @var array<string, string> [имя модуля => текст ошибки]
     */
    public static array $failed = [];

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

            try {
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

                    self::registerModuleConfig($moduleConfig);

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
            } catch (\Throwable $e) {
                // Битый модуль не должен ронять движок — пропускаем, остальные грузятся штатно.
                self::$failed[$module] = $e->getMessage();

                // Лог подавляем: одна запись на модуль в час, иначе ошибка пишется на каждый запрос.
                if (Cache::add('module_error_' . $module, true, 3600)) {
                    report($e);
                }
            }
        }
    }

    /**
     * Регистрирует в ядре то, что модуль объявил в module.php
     *
     * Вынесено отдельно, чтобы тесты модуля видели те же морф-типы, секции
     * и счётчики, что и работающий сайт
     *
     * @param array<string, mixed> $moduleConfig
     */
    public static function registerModuleConfig(array $moduleConfig): void
    {
        foreach ($moduleConfig['models'] ?? [] as $model => $config) {
            /** @var class-string $model */
            $morphName = $model::$morphName;
            Relation::morphMap([$morphName => $model]);

            if ($search = $config['search'] ?? null) {
                Registry::search($model, $search['view'], $search['with'] ?? []);
            }

            if ($feed = $config['feed'] ?? null) {
                Registry::feed($model, $feed);
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
                Registry::spamType($morphName);
            }

            // Счётчик раздела для /api/stats: true считает все записи модели,
            // замыкание возвращает свою выборку — разделам, где часть записей скрыта модерацией
            if ($stat = $config['stat'] ?? null) {
                $query = is_callable($stat) ? $stat : static fn (): Builder => $model::query();

                Registry::stat(
                    $morphName,
                    static fn (): int => $query()->count(),
                    static fn (): int => $query()->where('created_at', '>', now()->subDay())->count(),
                );
            }
        }

        // Секции модуля в /api/config
        foreach ($moduleConfig['api'] ?? [] as $section => $settings) {
            Registry::apiConfig($section, $settings);
        }

        // Наблюдатели за моделями
        foreach ($moduleConfig['observers'] ?? [] as $modelClass => $observerClass) {
            $modelClass::observe($observerClass);
        }
    }
}
