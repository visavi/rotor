<?php

declare(strict_types=1);

namespace App\Models;

use Exception;
use Illuminate\Filesystem\Filesystem;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
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
class Module extends BaseModel
{
    /**
     * Indicates if the model should be timestamped.
     */
    public $timestamps = false;

    /**
     * The attributes that aren't mass assignable.
     */
    protected $guarded = [];

    /**
     * Assets modules path
     */
    public string $assetsPath = '/assets/modules/';

    /**
     * Get the attributes that should be cast.
     */
    protected function casts(): array
    {
        return [
            'active'   => 'bool',
            'settings' => 'array',
        ];
    }

    /**
     * Выполняет применение миграции
     */
    public function migrate(): void
    {
        $migrationPath = base_path('modules/' . $this->name . '/migrations');

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
        $migrationPath = base_path('modules/' . $this->name . '/migrations');

        if (file_exists($migrationPath)) {
            $migrator = app('migrator');
            $nextBatchNumber = $migrator->getRepository()->getNextBatchNumber();
            $migrationNames = array_keys($migrator->getMigrationFiles($migrationPath));

            DB::table(config('database.migrations'))
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

        $filesystem = new Filesystem();
        $filesystem->link($assetsPath, $originPath);
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

        $filesystem = new Filesystem();
        $filesystem->delete($originPath);
    }

    /**
     * Получает название директории для симлинка
     */
    public function getLinkName(): string
    {
        return $this->assetsPath . Str::plural(strtolower($this->name));
    }

    /**
     * Получает название директории для симлинка из пути
     */
    public static function getLinkNameByPath(string $modulePath): string
    {
        return (new self())->assetsPath . Str::plural(strtolower(basename($modulePath)));
    }

    /**
     * Get enabled modules
     */
    public static function getEnabledModules(): array
    {
        return Cache::rememberForever('modules', static function () {
            try {
                return self::query()
                    ->where('active', true)
                    ->pluck('settings', 'name')
                    ->all();
            } catch (Exception) {
                return [];
            }
        });
    }
}
