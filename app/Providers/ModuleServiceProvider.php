<?php

namespace App\Providers;

use App\Models\Module;
use Illuminate\Routing\Router;
use Illuminate\Support\ServiceProvider;

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

        foreach ($modules as $module) {
            $moduleKey = strtolower($module);

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
        }
    }
}
