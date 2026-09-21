<?php

namespace Tests\Feature\Console;

use Illuminate\Support\Facades\File;
use Tests\TestCase;

class MakeModuleTest extends TestCase
{
    /**
     * Имя фикстуры выбрано так, чтобы не пересечься с реальным модулем
     */
    private const string MODULE = 'MakeModuleFixture';

    protected function tearDown(): void
    {
        File::deleteDirectory(base_path('modules/' . self::MODULE));

        parent::tearDown();
    }

    public function testCreatesSkeletonFiles(): void
    {
        $this->artisan('make:module', ['name' => self::MODULE])->assertSuccessful();

        $base = base_path('modules/' . self::MODULE . '/');

        $expected = [
            'module.php',
            'routes.php',
            'hooks.php',
            'changelog.md',
            'Http/Controllers/MakeModuleFixtureController.php',
            'Tests/Feature/MakeModuleFixtureSmokeTest.php',
            'resources/views/index.blade.php',
            'resources/lang/ru/make_module_fixture.php',
            'resources/lang/en/make_module_fixture.php',
            'resources/lang/ua/make_module_fixture.php',
        ];

        foreach ($expected as $file) {
            $this->assertFileExists($base . $file);
        }
    }

    public function testModelOptionCreatesModelWithMigration(): void
    {
        $this->artisan('make:module', ['name' => self::MODULE, '--model' => 'Task'])->assertSuccessful();

        $base = base_path('modules/' . self::MODULE . '/');

        $model = File::get($base . 'Models/Task.php');

        $this->assertStringContainsString('namespace Modules\\' . self::MODULE . '\\Models;', $model);
        $this->assertStringContainsString('class Task extends Model', $model);
        $this->assertStringContainsString("public static string \$morphName = 'tasks';", $model);

        $migrations = File::glob($base . 'database/migrations/*_create_tasks_table.php');
        $this->assertCount(1, $migrations);
        $this->assertStringContainsString("Schema::create('tasks'", File::get($migrations[0]));

        // Модель регистрируется в ядре через module.php
        $this->assertStringContainsString('Task::class', File::get($base . 'module.php'));
    }

    public function testAdminOptionCreatesSettingsPage(): void
    {
        $this->artisan('make:module', ['name' => self::MODULE, '--admin' => true])->assertSuccessful();

        $base = base_path('modules/' . self::MODULE . '/');
        $key = 'make_module_fixture';

        $this->assertFileExists($base . 'Http/Controllers/Admin/' . self::MODULE . 'SettingController.php');
        $this->assertFileExists($base . 'resources/views/admin/settings/_' . $key . '.blade.php');

        $migrations = File::glob($base . 'database/migrations/*_insert_' . $key . '_settings.php');
        $this->assertCount(1, $migrations);

        // Страница настроек доступна из маршрутов, админки и общей панели разделов
        $this->assertStringContainsString('SettingController::class', File::get($base . 'routes.php'));
        $this->assertStringContainsString('adminSettingsNav', File::get($base . 'hooks.php'));
        $this->assertStringContainsString("'actions'", File::get($base . 'module.php'));
    }

    public function testApiOptionCreatesApiControllerWithResource(): void
    {
        $this->artisan('make:module', [
            'name'    => self::MODULE,
            '--model' => 'Task',
            '--api'   => true,
        ])->assertSuccessful();

        $base = base_path('modules/' . self::MODULE . '/');

        $this->assertFileExists($base . 'Http/Controllers/Api/' . self::MODULE . 'ApiController.php');
        $this->assertFileExists($base . 'Http/Resources/TaskResource.php');
        $this->assertFileExists($base . 'openapi.json');

        $this->assertJson(File::get($base . 'openapi.json'));
        $this->assertStringContainsString('ApiController::class', File::get($base . 'routes.php'));
    }

    public function testApiOptionRequiresModel(): void
    {
        $this->artisan('make:module', ['name' => self::MODULE, '--api' => true])->assertFailed();

        $this->assertDirectoryDoesNotExist(base_path('modules/' . self::MODULE));
    }

    public function testOptionalFilesAreCreatedOnDemand(): void
    {
        $this->artisan('make:module', [
            'name'         => self::MODULE,
            '--helpers'    => true,
            '--config'     => true,
            '--middleware' => true,
        ])->assertSuccessful();

        $base = base_path('modules/' . self::MODULE . '/');

        $this->assertFileExists($base . 'helpers.php');
        $this->assertFileExists($base . 'config.php');
        $this->assertFileExists($base . 'middleware.php');
    }

    public function testOptionalFilesAreSkippedByDefault(): void
    {
        $this->artisan('make:module', ['name' => self::MODULE])->assertSuccessful();

        $base = base_path('modules/' . self::MODULE . '/');

        $this->assertFileDoesNotExist($base . 'helpers.php');
        $this->assertFileDoesNotExist($base . 'config.php');
        $this->assertFileDoesNotExist($base . 'middleware.php');
        $this->assertFileDoesNotExist($base . 'openapi.json');
    }

    public function testExistingModuleIsNotOverwrittenWithoutForce(): void
    {
        $this->artisan('make:module', ['name' => self::MODULE])->assertSuccessful();

        $marker = base_path('modules/' . self::MODULE . '/module.php');
        File::put($marker, '<?php return [];');

        $this->artisan('make:module', ['name' => self::MODULE])->assertFailed();
        $this->assertSame('<?php return [];', File::get($marker));

        $this->artisan('make:module', ['name' => self::MODULE, '--force' => true])->assertSuccessful();
        $this->assertStringContainsString("'version'", File::get($marker));
    }

    public function testRejectsNameThatIsNotStudlyCase(): void
    {
        $this->artisan('make:module', ['name' => 'my-module'])->assertFailed();

        $this->assertDirectoryDoesNotExist(base_path('modules/my-module'));
    }

    public function testGeneratedFilesAreValidPhpWithoutLeftoverPlaceholders(): void
    {
        $this->artisan('make:module', [
            'name'         => self::MODULE,
            '--model'      => 'Task',
            '--admin'      => true,
            '--api'        => true,
            '--helpers'    => true,
            '--config'     => true,
            '--middleware' => true,
        ])->assertSuccessful();

        $base = base_path('modules/' . self::MODULE);

        foreach (File::allFiles($base) as $file) {
            $contents = $file->getContents();

            $this->assertStringNotContainsString('{{ module', $contents, $file->getRelativePathname());

            // Пустые плейсхолдеры не должны оставлять хвост пустых строк — pint это ловит
            $this->assertMatchesRegularExpression('/[^\n]\n\z/', $contents, $file->getRelativePathname());

            if ($file->getExtension() === 'php') {
                exec('php -l ' . escapeshellarg($file->getPathname()), $output, $status);
                $this->assertSame(0, $status, implode(PHP_EOL, $output));
            }
        }
    }
}
