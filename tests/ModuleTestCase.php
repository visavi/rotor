<?php

namespace Tests;

use App\Http\Middleware\CheckInstallSite;
use App\Providers\ModuleServiceProvider;
use App\Support\Hook;
use App\Support\Registry;
use Illuminate\Contracts\Http\Kernel;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\File;
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

    /**
     * Переопределения config.php модуля для конкретного теста
     *
     * Применяются до подключения хелперов, хуков и маршрутов — как и в провайдере,
     * иначе код модуля увидел бы значения по умолчанию
     *
     * @return array<string, mixed>
     */
    protected function moduleConfig(): array
    {
        return [];
    }

    /**
     * Удаляет временный каталог теста
     *
     * Тесты чистят пути, которые в бою указывают на рабочие данные, поэтому
     * удаление за пределами storage/framework/testing считаем ошибкой теста
     */
    protected function deleteTestingDirectory(string $path): void
    {
        $testing = storage_path('framework/testing');

        if (! str_starts_with($path, $testing)) {
            $this->fail("Refusing to delete a non-testing directory: {$path}");
        }

        File::deleteDirectory($path);
    }

    private function registerModuleResources(): void
    {
        $name = $this->moduleName;
        $key = Str::snake($name);

        // config.php модуля в провайдере уезжает в Config::set($moduleKey, ...),
        // здесь то же самое и в том же порядке: без него config('<module>.*') в тестах пуст
        $moduleConfig = base_path("modules/{$name}/config.php");
        $values = file_exists($moduleConfig) ? include $moduleConfig : [];
        $values = is_array($values) ? array_merge($values, $this->moduleConfig()) : $this->moduleConfig();

        if ($values) {
            config()->set($key, $values);
        }

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
