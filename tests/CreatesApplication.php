<?php

namespace Tests;

use Illuminate\Contracts\Console\Kernel;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Bootstrap\LoadConfiguration;
use RuntimeException;

trait CreatesApplication
{
    /**
     * Замок прогона: держится открытым до конца процесса
     *
     * @var resource|null
     */
    private static $runLock;

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
        $this->acquireRunLock($app);

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
     * Страховка к DB_DATABASE из phpunit.xml: если имя базы всё же пришло
     * не тестовое, к нему дописывается _test.
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
     * Последний рубеж после forceTestDatabase(): migrate:fresh в RefreshDatabase
     * снёс бы боевую базу целиком.
     */
    private function guardTestDatabase(Application $app): void
    {
        $database = (string) $app->make('db')->connection()->getDatabaseName();

        if (str_contains($database, 'test') || $database === ':memory:') {
            return;
        }

        throw new RuntimeException(sprintf(
            'Тесты подключены к базе «%s», а не к тестовой.',
            $database,
        ));
    }

    /**
     * Не даёт запустить второй прогон, пока идёт первый
     *
     * Тестовая база одна на всех: migrate:fresh второго прогона сносит таблицы
     * под первым, и тот падает ложным «Table ... doesn't exist». Замок снимает
     * ОС при выходе процесса, в том числе убитого
     */
    private function acquireRunLock(Application $app): void
    {
        if (self::$runLock !== null) {
            return;
        }

        $handle = fopen($app->storagePath('framework/testing/run.lock'), 'c');

        if ($handle === false || ! flock($handle, LOCK_EX | LOCK_NB)) {
            throw new RuntimeException(
                'Тесты уже запущены в другом процессе: тестовая база одна на всех, дождитесь его завершения.'
            );
        }

        self::$runLock = $handle;
    }
}
