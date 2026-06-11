<?php

namespace Tests;

use App\Http\Middleware\CheckInstallSite;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;

abstract class ModuleTestCase extends TestCase
{
    use RefreshDatabase;

    protected string $moduleName;

    protected function setUp(): void
    {
        parent::setUp();

        $this->withoutMiddleware(CheckInstallSite::class);

        // Таблицы модулей создаются единым migrate:fresh (пути — в CreatesApplication)
        $this->registerModuleResources();
    }

    private function registerModuleResources(): void
    {
        $name = $this->moduleName;
        $key = Str::snake($name);

        $viewsPath = base_path("modules/{$name}/resources/views");
        if (is_dir($viewsPath)) {
            $this->app['view']->addNamespace($key, $viewsPath);
        }

        $langPath = base_path("modules/{$name}/resources/lang");
        if (is_dir($langPath)) {
            $this->app['translator']->addNamespace($key, $langPath);
        }

        $routesFile = base_path("modules/{$name}/routes.php");
        if (file_exists($routesFile)) {
            require $routesFile;
            $this->app['router']->getRoutes()->refreshNameLookups();
            $this->app['router']->getRoutes()->refreshActionLookups();
        }
    }
}
