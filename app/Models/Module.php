<?php

declare(strict_types=1);

namespace App\Models;

use Exception;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Str;

/**
 * Class Module
 *
 * @property int    $id
 * @property string $name
 * @property string $version
 * @property bool   $active
 * @property int    $updated_at
 * @property int    $created_at
 */
class Module extends Model
{
    /**
     * Assets modules path
     */
    protected const ASSETS_PATH = '/assets/modules/';

    /**
     * Indicates if the model should be timestamped.
     */
    public $timestamps = false;

    /**
     * The attributes that aren't mass assignable.
     */
    protected $guarded = [];

    /**
     * Get the attributes that should be cast.
     */
    protected function casts(): array
    {
        return [
            'active' => 'bool',
        ];
    }

    /**
     * Выполняет применение миграции
     */
    public function migrate(): void
    {
        $migrationPath = base_path('modules/' . $this->name . '/database/migrations');

        if (file_exists($migrationPath)) {
            Artisan::call('migrate', [
                '--force'    => true,
                '--realpath' => true,
                '--path'     => $migrationPath,
            ]);
        }
    }

    /**
     * Выполняет откат миграций
     */
    public function rollback(): void
    {
        $migrationPath = base_path('modules/' . $this->name . '/database/migrations');

        if (file_exists($migrationPath)) {
            $migrator = app('migrator');
            $nextBatchNumber = $migrator->getRepository()->getNextBatchNumber();
            $migrationNames = array_keys($migrator->getMigrationFiles($migrationPath));

            DB::table(config('database.migrations.table'))
                ->whereIn('migration', $migrationNames)
                ->update(['batch' => $nextBatchNumber]);

            Artisan::call('migrate:rollback', [
                '--force'    => true,
                '--realpath' => true,
                '--path'     => $migrationPath,
            ]);
        }
    }

    /**
     * Создает симлинки модулей
     */
    public function createSymlink(): void
    {
        $originPath = public_path($this->getLinkName());
        if (file_exists($originPath)) {
            return;
        }

        $assetsPath = base_path('modules/' . $this->name . '/resources/assets');
        if (! file_exists($assetsPath)) {
            return;
        }

        File::link($assetsPath, $originPath);
    }

    /**
     * Удаляет симлинки модулей
     */
    public function deleteSymlink(): void
    {
        $originPath = public_path($this->getLinkName());
        if (! file_exists($originPath)) {
            return;
        }

        File::delete($originPath);
    }

    /**
     * Синхронизирует все активные модули с ядром: симлинки и публикация файлов.
     *
     * Каждый модуль обёрнут в try/catch — битый модуль (ошибка в module.php,
     * проблема с правами на симлинк и т.п.) не роняет синхронизацию остальных.
     * Полная синхронизация делает публикацию независимой от порядка установки:
     * напр. перевод модуля-языка подмешается в Форум, даже если Форум поставили позже.
     *
     * @return array<string, string> [имя модуля => текст ошибки]
     */
    public static function syncAll(): array
    {
        $failed = [];

        foreach (self::query()->where('active', true)->get() as $module) {
            try {
                $module->createSymlink();
                $module->publish();
            } catch (\Throwable $e) {
                $failed[$module->name] = $e->getMessage();
                report($e);
            }
        }

        clearCache(['modules', 'settings']);

        return $failed;
    }

    /**
     * Копирует файлы модуля в директории движка
     */
    public function publish(): void
    {
        foreach ($this->getPublishMap() as $from => $to) {
            if (! file_exists($from)) {
                continue;
            }

            if (is_dir($from)) {
                File::copyDirectory($from, $to);
            } else {
                File::ensureDirectoryExists(dirname($to));
                File::copy($from, $to);
            }
        }
    }

    /**
     * Удаляет ранее скопированные файлы модуля
     */
    public function unpublish(): void
    {
        foreach ($this->getPublishMap() as $from => $to) {
            if (is_dir($from)) {
                File::deleteDirectory($to);
            } elseif (is_file($from)) {
                File::delete($to);
            }
        }
    }

    /**
     * Карта публикации файлов модуля [источник => назначение]
     */
    private function getPublishMap(): array
    {
        $configFile = base_path('modules/' . $this->name . '/module.php');
        if (! file_exists($configFile)) {
            return [];
        }

        $config = include $configFile;

        $map = [];
        foreach ($config['publish'] ?? [] as $from => $to) {
            // Защита от выхода за пределы директорий
            if (str_contains($from, '..') || str_contains($to, '..')) {
                continue;
            }

            // Публикация в другой модуль только если он есть на диске
            // (напр. модуль-язык подмешивает перевод в modules/Forum/...)
            if (preg_match('#^modules/([^/]+)/#', $to, $match)
                && ! is_dir(base_path('modules/' . $match[1]))) {
                continue;
            }

            $map[base_path('modules/' . $this->name . '/' . $from)] = base_path($to);
        }

        return $map;
    }

    /**
     * Получает название директории для симлинка
     */
    public function getLinkName(): string
    {
        return self::ASSETS_PATH . Str::plural(strtolower($this->name));
    }

    /**
     * Получает название директории для симлинка из пути
     */
    public static function getLinkNameByPath(string $modulePath): string
    {
        return self::ASSETS_PATH . Str::plural(strtolower(basename($modulePath)));
    }

    /**
     * Количество установленных модулей с доступным обновлением
     */
    public static function updatesCount(): int
    {
        $installed = self::query()->pluck('version', 'name')->all();
        if (! $installed) {
            return 0;
        }

        $registry = ModuleRegistry::getAvailableModules();

        $count = 0;
        foreach ($installed as $name => $version) {
            $configFile = base_path('modules/' . $name . '/module.php');
            if (! file_exists($configFile)) {
                continue;
            }

            $config = include $configFile;
            $localVersion = $config['version'] ?? null;
            $registryVersion = $registry[$name]['version'] ?? null;

            $hasUpdate = ($localVersion && version_compare($localVersion, $version, '>'))
                || ($registryVersion && version_compare($registryVersion, $version, '>'));

            if ($hasUpdate) {
                $count++;
            }
        }

        return $count;
    }

    /**
     * Get enabled modules
     *
     * Кеш привязан к версии движка: после обновления ядра структура данных
     * могла измениться, поэтому устаревший кеш пересобирается
     */
    public static function getEnabledModules(): array
    {
        $cached = Cache::get('modules', []);

        if (($cached['version'] ?? null) === ROTOR_VERSION && ! empty($cached['modules'])) {
            return $cached['modules'];
        }

        $modules = self::loadEnabledModules();

        if ($modules !== []) {
            Cache::forever('modules', [
                'version' => ROTOR_VERSION,
                'modules' => $modules,
            ]);
        }

        return $modules;
    }

    /**
     * Загружает активные модули с их файловой структурой
     */
    private static function loadEnabledModules(): array
    {
        try {
            $names = self::query()
                ->where('active', true)
                ->pluck('name')
                ->all();
        } catch (Exception) {
            return [];
        }

        $result = [];
        foreach ($names as $name) {
            $result[$name] = [
                'files'  => self::scanModuleFiles($name),
                'config' => self::loadModuleConfig($name),
            ];
        }

        return $result;
    }

    /**
     * Загружает config модуля
     */
    private static function loadModuleConfig(string $name): ?array
    {
        $configFile = base_path('modules/' . $name . '/config.php');
        if (! file_exists($configFile)) {
            return null;
        }

        $config = include $configFile;

        return is_array($config) ? $config : null;
    }

    /**
     * Сканирует наличие файлов модуля
     */
    private static function scanModuleFiles(string $name): array
    {
        $base = base_path('modules/' . $name . '/');

        return [
            'views'      => is_dir($base . 'resources/views'),
            'lang'       => is_dir($base . 'resources/lang'),
            'helpers'    => file_exists($base . 'helpers.php'),
            'hooks'      => file_exists($base . 'hooks.php'),
            'routes'     => file_exists($base . 'routes.php'),
            'middleware' => file_exists($base . 'middleware.php'),
            'module'     => file_exists($base . 'module.php'),
            'commands'   => array_map(
                static fn ($file) => 'Modules\\' . $name . '\\Console\\' . basename($file, '.php'),
                glob($base . 'Console/*.php') ?: []
            ),
        ];
    }
}
