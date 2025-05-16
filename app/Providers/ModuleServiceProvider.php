<?php

namespace App\Providers;

use App\Models\Module;
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
    public function boot(): void
    {
        $modules = Module::getEnabledModules();

        foreach ($modules as $module) {
            $hooksFile = base_path('modules/' . $module . '/hooks.php');
            if (file_exists($hooksFile)) {
                include_once $hooksFile;
            }

            $routesFile = base_path('modules/' . $module . '/routes.php');
            if (file_exists($routesFile)) {
                $this->loadRoutesFrom($routesFile);
            }

            $viewsPath = base_path('modules/' . $module . '/resources/views');
            if (file_exists($viewsPath)) {
                $this->loadViewsFrom($viewsPath, $module);
            }

            $langPath = base_path('modules/' . $module . '/resources/lang');
            if (file_exists($langPath)) {
                $this->loadTranslationsFrom($langPath, $module);
            }

            $configFile = base_path('modules/' . $module . '/config.php');
            if (file_exists($configFile)) {
                $this->mergeConfigFrom($configFile, $module);
            }
        }
    }
}
