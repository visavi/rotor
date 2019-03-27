<?php

declare(strict_types=1);

namespace App\Models;

use Phinx\Config\Config;
use Phinx\Console\Command\Migrate;
use Phinx\Console\PhinxApplication;
use Phinx\Wrapper\TextWrapper;

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
     * Выполняет применение миграции
     *
     * @param string $migrationPath
     */
    public function migrate(string $migrationPath): void
    {
        if (file_exists($migrationPath)) {
            $app = new PhinxApplication;
            $app->add(new Migrate());

            /** @var Migrate $command */
            $command = $app->find('migrate');

            $config = require APP . '/migration.php';
            $config['paths']['migrations'] = $migrationPath;

            $command->setConfig(new Config($config));

            $wrap = new TextWrapper($app);
            $wrap->getMigrate();
        }
    }


    /**
     * Выполняет откат миграций
     *
     * @param string $migrationPath
     */
    public function rollback(string $migrationPath): void
    {
        if (file_exists($migrationPath)) {
            $app = new PhinxApplication;
            $app->add(new Migrate());

            /** @var Migrate $command */
            $command = $app->find('rollback');

            $config = require APP . '/migration.php';
            $config['paths']['migrations'] = $migrationPath;

            $command->setConfig(new Config($config));

            $wrap = new TextWrapper($app);
            $wrap->getRollback(null, 0);
        }
    }

    /**
     * Создает симлинки модулей
     *
     * @param string $path
     * @param array  $module
     */
    public function createSymlinks(string $path, array $module): void
    {
        if (isset($module['symlinks'])) {
            foreach ($module['symlinks'] as $key => $symlink) {
                if (file_exists($symlink)) {
                    unlink($symlink);
                }

                symlink($path . '/' . $key, $symlink);
            }
        }
    }

    /**
     * Удаляет симлинки модулей
     *
     * @param array $module
     */
    public function deleteSymlinks(array $module): void
    {
        if (isset($module['symlinks'])) {
            foreach ($module['symlinks'] as $key => $symlink) {
                if (file_exists($symlink)) {
                    unlink($symlink);
                }
            }
        }
    }
}
