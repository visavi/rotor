<?php

namespace Tests;

use Illuminate\Contracts\Console\Kernel;
use Illuminate\Foundation\Bootstrap\LoadConfiguration;
use Illuminate\Foundation\Application;
use RuntimeException;

trait CreatesApplication
{
    public function createApplication(): Application
    {
        $app = require __DIR__ . '/../bootstrap/app.php';

        // Правку надо внести до старта провайдеров: ModuleServiceProvider::boot()
        // читает список модулей из БД и тем самым создаёт соединение
        $app->afterBootstrapping(LoadConfiguration::class, function (Application $app): void {
            $this->forceTestDatabase($app);
        });

        $app->make(Kernel::class)->bootstrap();

        $this->guardTestDatabase($app);

        // Регистрируем миграции модулей, чтобы migrate:fresh применял их в общем
        // прогоне RefreshDatabase один раз, а не в каждом тесте отдельно
        $migrator = $app->make('migrator');
        foreach (glob(base_path('modules/*/database/migrations'), GLOB_ONLYDIR) ?: [] as $path) {
            $migrator->path($path);
        }

        return $app;
    }

    /**
     * Принудительно уводит соединение на тестовую базу
     *
     * DB_DATABASE из phpunit.xml применяется только когда Laravel читает env().
     * При закэшированном конфиге (bootstrap/cache/config.php) движок берёт готовый
     * массив, env() не вызывает, и прогон уходит на боевую базу. Правка загруженного
     * конфига работает в обоих случаях.
     *
     * purge() здесь не нужен и вреден: соединение к этому моменту ещё не создано,
     * а переподключение посреди загрузки ломает откат транзакций RefreshDatabase.
     */
    private function forceTestDatabase(Application $app): void
    {
        $key = 'database.connections.' . $app['config']->get('database.default') . '.database';
        $database = (string) $app['config']->get($key);

        if ($database === '' || $database === ':memory:' || str_contains($database, 'test')) {
            return;
        }

        $app['config']->set($key, $database . '_test');
    }

    /**
     * Не даёт прогону тестов уйти на боевую базу
     *
     * При закэшированном конфиге (bootstrap/cache/config.php) Laravel читает
     * готовый массив и не вызывает env() — DB_DATABASE из phpunit.xml не
     * применяется, и прогон молча уходит на боевую БД: migrate:fresh сносит её
     * целиком, а truncate() в тестах даёт неявный COMMIT и переживает откат
     * RefreshDatabase. Кэш возвращается сам при APP_ENV=production, потому что
     * refreshCaches() после любого действия с модулем зовёт config:cache, —
     * так что перед прогоном нужен config:clear.
     *
     * Переподключать соединение прямо здесь нельзя: purge() посреди загрузки
     * приложения ломает откат транзакций RefreshDatabase, и тесты начинают
     * оставлять данные друг другу.
     */
    private function guardTestDatabase(Application $app): void
    {
        $database = (string) $app->make('db')->connection()->getDatabaseName();

        if (str_contains($database, 'test') || $database === ':memory:') {
            return;
        }

        throw new RuntimeException(sprintf(
            'Тесты подключены к базе «%s», а не к тестовой. Выполните php artisan config:clear: '
            . 'при закэшированном конфиге DB_DATABASE из phpunit.xml не применяется.',
            $database,
        ));
    }
}
