<?php

namespace Tests;

use Illuminate\Foundation\Testing\RefreshDatabase;

abstract class ModuleTestCase extends TestCase
{
    use RefreshDatabase;

    protected string $moduleName;

    protected function setUp(): void
    {
        parent::setUp();

        if (! is_dir(base_path("modules/{$this->moduleName}"))) {
            $this->markTestSkipped("Module [{$this->moduleName}] is not installed.");
        }
    }

    protected function defineDatabaseMigrations(): void
    {
        $path = base_path("modules/{$this->moduleName}/database/migrations");

        if (! is_dir($path)) {
            return;
        }

        $this->artisan('migrate', ['--path' => $path, '--realpath' => true]);

        $this->beforeApplicationDestroyed(function () use ($path) {
            $this->artisan('migrate:rollback', ['--path' => $path, '--realpath' => true]);
        });
    }
}
