<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Support\Str;
use Phinx\Config\Config;
use Phinx\Console\Command\Migrate;
use Phinx\Console\PhinxApplication;
use Phinx\Wrapper\TextWrapper;
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
    public $assetsPath = HOME . '/assets/modules/';

    /**
     * Выполняет применение миграции
     *
     * @param string $modulePath
     */
    public function migrate(string $modulePath): void
    {
        $migrationPath = $modulePath . '/migrations';

        if (file_exists($migrationPath)) {
            $app = new PhinxApplication();
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
     * @param string $modulePath
     */
    public function rollback(string $modulePath): void
    {
        $migrationPath = $modulePath . '/migrations';

        if (file_exists($migrationPath)) {
            $app = new PhinxApplication();
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
     * @param string $modulePath
     */
    public function createSymlink(string $modulePath): void
    {
        $filesystem  = new Filesystem();
        $originPath  = $this->getLinkName($modulePath);
        $modulesPath = $modulePath . '/resources/assets';

        if (! file_exists($modulesPath)) {
            return;
        }

        $relativePath = $filesystem->makePathRelative($modulesPath, $this->assetsPath);

        $filesystem->symlink($relativePath, $originPath, true);
    }

    /**
     * Удаляет симлинки модулей
     *
     * @param string $modulePath
     */
    public function deleteSymlink(string $modulePath): void
    {
        $link = $this->getLinkName($modulePath);

        if (is_link($link)) {
            unlink($link);
        }
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
}
