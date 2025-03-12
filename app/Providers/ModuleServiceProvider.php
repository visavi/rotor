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
            $routesFile = base_path('modules/' . $module . '/routes.php');
            if (file_exists($routesFile)) {
                include_once $routesFile;
            }

            $hooksFile = base_path('modules/' . $module . '/hooks.php');
            if (file_exists($hooksFile)) {
                include_once $hooksFile;
            }

            $this->loadViewsFrom(base_path('modules/' . $module . '/resources/views'), $module);
            $this->loadTranslationsFrom(base_path('modules/' . $module . '/resources/lang'), $module);
        }
    }
}
