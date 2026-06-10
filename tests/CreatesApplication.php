<?php

namespace Tests;

use Illuminate\Contracts\Console\Kernel;
use Illuminate\Foundation\Application;

trait CreatesApplication
{
    public function createApplication(): Application
    {
        $app = require __DIR__ . '/../bootstrap/app.php';

        $app->make(Kernel::class)->bootstrap();

        // Регистрируем миграции модулей, чтобы migrate:fresh применял их в общем
        // прогоне RefreshDatabase один раз, а не в каждом тесте отдельно
        $migrator = $app->make('migrator');
        foreach (glob(base_path('modules/*/database/migrations'), GLOB_ONLYDIR) ?: [] as $path) {
            $migrator->path($path);
        }

        return $app;
    }
}
