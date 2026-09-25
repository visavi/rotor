<?php

declare(strict_types=1);

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Str;
use Symfony\Component\Console\Command\Command as SymfonyCommand;

class MakeModule extends Command
{
    /**
     * The name and signature of the console command.
     */
    protected $signature = 'make:module {name : Название модуля в StudlyCase, напр. Kanban}
                            {--model= : Создать модель с миграцией, напр. Task}
                            {--admin : Создать страницу настроек в админке}
                            {--api : Создать api-контроллер, ресурс и openapi.json (требует --model)}
                            {--helpers : Создать helpers.php}
                            {--config : Создать config.php}
                            {--middleware : Создать middleware.php}
                            {--force : Перезаписать существующий модуль}';

    /**
     * The description of the console command.
     */
    protected $description = 'Create a new module skeleton in the modules directory';

    /**
     * Переводы страницы настроек — добавляются к title при --admin
     */
    private const array LANG_ADMIN = [
        'ru' => ['settings' => 'Настройки', 'settings_per_page' => 'Записей на страницу'],
        'en' => ['settings' => 'Settings', 'settings_per_page' => 'Records per page'],
        'ua' => ['settings' => 'Налаштування', 'settings_per_page' => 'Записів на сторінку'],
    ];

    /**
     * Подстановки для стабов
     *
     * @var array<string, string>
     */
    private array $replacements = [];

    /**
     * Базовый путь создаваемого модуля
     */
    private string $basePath;

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $name = (string) $this->argument('name');

        if (! preg_match('/^[A-Z][A-Za-z0-9]*$/', $name)) {
            $this->components->error('Название модуля должно быть в StudlyCase, напр. Kanban');

            return SymfonyCommand::FAILURE;
        }

        $this->basePath = base_path('modules/' . $name);

        if (File::exists($this->basePath) && ! $this->option('force')) {
            $this->components->error(sprintf('Модуль "%s" уже существует, перезапись — с флагом --force', $name));

            return SymfonyCommand::FAILURE;
        }

        $model = (string) $this->option('model');

        if ($model !== '' && ! preg_match('/^[A-Z][A-Za-z0-9]*$/', $model)) {
            $this->components->error('Название модели должно быть в StudlyCase, напр. Task');

            return SymfonyCommand::FAILURE;
        }

        if ($this->option('api') && $model === '') {
            $this->components->error('Флаг --api требует --model: ресурс и роуты строятся вокруг модели');

            return SymfonyCommand::FAILURE;
        }

        $key = Str::snake($name);
        $title = Str::headline($name);
        $table = $model === '' ? '' : Str::snake(Str::plural($model));

        $this->replacements = [
            '{{ module }}'      => $name,
            '{{ moduleKey }}'   => $key,
            '{{ moduleRoute }}' => str_replace('_', '-', $key),
            '{{ moduleTitle }}' => $title,
            '{{ requires }}'    => ROTOR_VERSION,
            '{{ model }}'       => $model,
            '{{ modelTable }}'  => $table,
            '{{ modelUse }}'    => $model === '' ? '' : sprintf("\nuse Modules\\%s\\Models\\%s;\n", $name, $model),
            '{{ models }}'      => $this->modelsSection($model, $key),
        ] + $this->adminReplacements($name, $key)
          + $this->apiReplacements($name, $table);

        foreach ($this->files($name, $model, $table) as $stub => $path) {
            $extra = [];

            if (preg_match('/^lang\.(\w+)\.php$/', $stub, $match)) {
                $extra['{{ langItems }}'] = $this->langItems($match[1], $title);
            }

            $this->createFile($stub, $path, $extra);
        }

        $this->newLine();
        $this->components->info(sprintf('Модуль "%s" создан. Включите его в разделе /admin/modules', $name));

        return SymfonyCommand::SUCCESS;
    }

    /**
     * Карта файлов модуля [стаб => путь относительно модуля]
     *
     * @return array<string, string>
     */
    private function files(string $name, string $model, string $table): array
    {
        $files = [
            'module.php'     => 'module.php',
            'routes.php'     => 'routes.php',
            'hooks.php'      => 'hooks.php',
            'changelog.md'   => 'changelog.md',
            'controller.php' => 'Http/Controllers/' . $name . 'Controller.php',
            'test.php'       => 'Tests/Feature/' . $name . 'SmokeTest.php',
            'view.blade'     => 'resources/views/index.blade.php',
            'lang.ru.php'    => 'resources/lang/ru/' . Str::snake($name) . '.php',
            'lang.en.php'    => 'resources/lang/en/' . Str::snake($name) . '.php',
            'lang.ua.php'    => 'resources/lang/ua/' . Str::snake($name) . '.php',
        ];

        if ($this->option('admin')) {
            $key = Str::snake($name);

            $files['settingcontroller.php'] = 'Http/Controllers/Admin/' . $name . 'SettingController.php';
            $files['settings.blade'] = 'resources/views/admin/settings/_' . $key . '.blade.php';
            $files['migration.settings.php'] = sprintf(
                'database/migrations/%s_insert_%s_settings.php',
                date('Y_m_d_His'),
                $key,
            );
        }

        if ($this->option('api')) {
            $files['apicontroller.php'] = 'Http/Controllers/Api/' . $name . 'ApiController.php';
            $files['resource.php'] = 'Http/Resources/' . $model . 'Resource.php';
            $files['openapi.json'] = 'openapi.json';
        }

        foreach (['helpers', 'config', 'middleware'] as $option) {
            if ($this->option($option)) {
                $files[$option . '.php'] = $option . '.php';
            }
        }

        if ($model !== '') {
            $files['model.php'] = 'Models/' . $model . '.php';
            $files['migration.create.php'] = sprintf(
                'database/migrations/%s_create_%s_table.php',
                date('Y_m_d_His'),
                $table,
            );
        }

        return $files;
    }

    /**
     * Подстановки страницы настроек — пустые, если модуль без админки
     *
     * @return array<string, string>
     */
    private function adminReplacements(string $name, string $key): array
    {
        $route = str_replace('_', '-', $key);

        if (! $this->option('admin')) {
            return [
                '{{ adminRoutesUse }}' => '',
                '{{ adminRoutes }}'    => '',
                '{{ adminHook }}'      => '',
                '{{ actions }}'        => '',
            ];
        }

        return [
            '{{ adminRoutesUse }}' => sprintf("use Modules\\%s\\Http\\Controllers\\Admin\\%sSettingController;\n", $name, $name),

            '{{ adminRoutes }}' => <<<PHP

                Route::middleware(['web', 'check.admin:boss', 'admin.logger'])
                    ->prefix('admin')
                    ->controller({$name}SettingController::class)
                    ->name('{$route}.')
                    ->group(function () {
                        Route::get('/{$route}-settings', 'index')->name('settings');
                        Route::post('/{$route}-settings', 'update')->name('settings.update');
                    });

                PHP,

            '{{ adminHook }}' => <<<PHP

                // Ссылка в навигации настроек админки
                Hook::add('adminSettingsNav', static fn () => '<a class="nav-link" href="' . route('{$route}.settings') . '">' . __('{$key}::{$key}.settings') . '</a>');

                PHP,

            '{{ actions }}' => <<<PHP

                    'actions' => [
                        '/admin/{$route}-settings' => '{$key}::{$key}.settings',
                    ],

                PHP,
        ];
    }

    /**
     * Подстановки api-роутов — пустые, если модуль без api
     *
     * @return array<string, string>
     */
    private function apiReplacements(string $name, string $table): array
    {
        if (! $this->option('api')) {
            return [
                '{{ apiRoutesUse }}' => '',
                '{{ apiRoutes }}'    => '',
            ];
        }

        return [
            '{{ apiRoutesUse }}' => sprintf(
                "use Modules\\%s\\Http\\Controllers\\Api\\%sApiController;\n",
                $name,
                $name,
            ),

            '{{ apiRoutes }}' => <<<PHP

                // Токен необязателен: гость читает, авторизованный получает свои данные
                Route::middleware(['api', 'check.token.optional'])
                    ->prefix('api')
                    ->controller({$name}ApiController::class)
                    ->group(function () {
                        Route::get('/{$table}', 'index');
                        Route::get('/{$table}/{id}', 'view');
                    });

                PHP,
        ];
    }

    /**
     * Строки перевода модуля с выравниванием стрелок, как в остальных модулях
     */
    private function langItems(string $locale, string $title): string
    {
        $items = ['title' => $title];

        if ($this->option('admin')) {
            $items += self::LANG_ADMIN[$locale];
        }

        $width = max(array_map('strlen', array_keys($items))) + 2;

        $lines = '';
        foreach ($items as $name => $value) {
            $lines .= sprintf("    %-{$width}s => '%s',\n", "'" . $name . "'", $value);
        }

        return $lines;
    }

    /**
     * Секция models для module.php — через неё ядро узнаёт о модели модуля
     */
    private function modelsSection(string $model, string $key): string
    {
        if ($model === '') {
            return '';
        }

        return <<<PHP

                'models' => [
                    {$model}::class => [
                        'label' => '{$key}::{$key}.title',
                    ],
                ],

            PHP;
    }

    /**
     * Создает файл модуля из стаба
     */
    private function createFile(string $stub, string $path, array $extra = []): void
    {
        $stubPath = resource_path('stubs/module/' . $stub . '.stub');

        // Незаполненные плейсхолдеры оставляют хвост пустых строк — pint такое не пропускает
        $content = rtrim(strtr(File::get($stubPath), $extra + $this->replacements)) . PHP_EOL;

        $target = $this->basePath . '/' . $path;

        File::ensureDirectoryExists(dirname($target));
        File::put($target, $content);

        $this->components->twoColumnDetail('modules/' . basename($this->basePath) . '/' . $path, '<fg=green>создан</>');
    }
}
