<?php

declare(strict_types=1);

namespace App\Models;

use Exception;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Symfony\Component\Filesystem\Filesystem;

/**
 * Class Module
 *
 * @property int id
 * @property string version
 * @property string name
 * @property int disabled
 * @property int updated_at
 * @property int created_at
 */
class Module extends BaseModel
{
    /**
     * Indicates if the model should be timestamped.
     *
     * @var bool
     */
    public $timestamps = false;

    /**
     * The attributes that aren't mass assignable.
     *
     * @var array
     */
    protected $guarded = [];

    /**
     * Assets modules path
     */
    public $assetsPath = '/assets/modules/';

    /**
     * Выполняет применение миграции
     *
     * @param string $modulePath
     */
    public function migrate(string $modulePath): void
    {
        $migrationPath = $modulePath . '/migrations';

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
     *
     * @param string $modulePath
     */
    public function rollback(string $modulePath): void
    {
        $migrationPath = $modulePath . '/migrations';

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
     *
     * @param string $modulePath
     */
    public function createSymlink(string $modulePath): void
    {
        $filesystem  = new Filesystem();
        $originPath  = public_path($this->getLinkName($modulePath));
        $modulesPath = $modulePath . '/resources/assets';

        if (! file_exists($modulesPath)) {
            return;
        }

        if (function_exists('symlink')) {
            $filesystem->symlink($modulesPath, $originPath, true);
        } else {
            $filesystem->mirror($modulesPath, $originPath, null, ['override' => true, 'delete' => true]);
        }
    }

    /**
     * Удаляет симлинки модулей
     *
     * @param string $modulePath
     */
    public function deleteSymlink(string $modulePath): void
    {
        $originPath = public_path($this->getLinkName($modulePath));

        if (! file_exists($originPath)) {
            return;
        }

        $filesystem = new Filesystem();
        $filesystem->remove($originPath);
    }

    /**
     * Получает название директории для симлинка
     *
     * @param string $modulePath
     * @return string
     */
    public function getLinkName(string $modulePath): string
    {
        return $this->assetsPath . Str::plural(strtolower(basename($modulePath)));
    }

    /**
     * Get enabled modules
     *
     * @return array
     */
    public static function getEnabledModules(): array
    {
        try {
            $modules = self::query()->where('disabled', 0)->pluck('name')->all();
        } catch (Exception) {
            $modules = [];
        }

        return $modules;
    }
}
