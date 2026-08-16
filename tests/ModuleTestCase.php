<?php

namespace Tests;

use App\Http\Middleware\CheckInstallSite;
use App\Providers\ModuleServiceProvider;
use App\Support\Hook;
use App\Support\Registry;
use Illuminate\Contracts\Http\Kernel;
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

        // Функции переобъявить нельзя, поэтому хелперы грузятся один раз на процесс
        $helpersFile = base_path("modules/{$name}/helpers.php");
        if (file_exists($helpersFile)) {
            require_once $helpersFile;
        }

        // Хуки живут в статике весь прогон: чужие убираем, свои ставим заново,
        // иначе шаблоны соседнего модуля подмешаются в этот тест
        Hook::flush();
        Registry::flush();

        $hooksFile = base_path("modules/{$name}/hooks.php");
        if (file_exists($hooksFile)) {
            require $hooksFile;
        }

        // Своя прослойка модуля: алиасы и группа web, как в провайдере
        $middlewareFile = base_path("modules/{$name}/middleware.php");
        if (file_exists($middlewareFile)) {
            $middleware = include $middlewareFile;

            foreach ($middleware['aliases'] ?? [] as $alias => $class) {
                $this->app['router']->aliasMiddleware($alias, $class);
            }

            // Группы приходят из ядра при его инициализации и затирают запись
            // напрямую в роутер, поэтому прослойку добавляем самому ядру
            $kernel = $this->app->make(Kernel::class);

            foreach ($middleware['web'] ?? [] as $class) {
                $kernel->appendMiddlewareToGroup('web', $class);
            }
        }

        $routesFile = base_path("modules/{$name}/routes.php");
        if (file_exists($routesFile)) {
            require $routesFile;
            $this->app['router']->getRoutes()->refreshNameLookups();
            $this->app['router']->getRoutes()->refreshActionLookups();
        }

        // Морф-типы, секции /api/config и счётчики — те же, что на работающем сайте
        $configFile = base_path("modules/{$name}/module.php");
        if (file_exists($configFile)) {
            ModuleServiceProvider::registerModuleConfig(include $configFile);
        }
    }
}
