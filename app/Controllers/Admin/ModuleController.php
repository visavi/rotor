<?php

declare(strict_types=1);

namespace App\Controllers\Admin;

use App\Models\Module;
use App\Models\User;
use Illuminate\Http\Request;
use Phinx\Config\Config;
use Phinx\Console\Command\Migrate;
use Phinx\Console\PhinxApplication;
use Phinx\Wrapper\TextWrapper;

class ModuleController extends AdminController
{
    /**
     * ModuleController constructor.
     */
    public function __construct()
    {
        parent::__construct();

        if (! isAdmin(User::BOSS)) {
            abort(403, 'Доступ запрещен!');
        }
    }

    /**
     * Главная страница
     *
     * @return string
     */
    public function index(): string
    {
        $modules      = glob(APP . '/Modules/*', GLOB_ONLYDIR);
        $moduleActive = Module::query()->pluck('version', 'name')->all();

        $moduleNames = [];
        foreach ($modules as $module) {
            if (file_exists($module . '/module.php')) {
                $moduleNames[basename($module)] = include $module . '/module.php';
            }
        }

        return view('admin/modules/index', compact('moduleNames', 'moduleActive'));
    }

    /**
     * Просмотр модуля
     *
     * @param Request $request
     * @return string
     */
    public function module(Request $request): string
    {
        $moduleName = check($request->input('module'));
        $modulePath = APP . '/Modules/' . $moduleName;

        if (! preg_match('|^[A-Z][\w\-]+$|', $moduleName) || ! file_exists($modulePath)) {
            abort('default', 'Данный модуль не найден!');
        }

        $moduleActive = Module::query()->where('name', $moduleName)->first();

        $module = include $modulePath . '/module.php';

        if (file_exists($modulePath . '/screenshots')) {
            $module['screenshots'] = glob($modulePath . '/screenshots/*.{gif,png,jpg,jpeg}', GLOB_BRACE);
        }

        if (file_exists($modulePath . '/migrations')) {
            $module['migrations'] = array_map('basename', glob($modulePath . '/migrations/*.php'));
        }

        if ($module['symlinks']) {
            $module['symlinks'] = array_map(function ($symlink) {
                return str_replace(HOME, '', $symlink);
            }, $module['symlinks']);
        }

        return view('admin/modules/module', compact('module', 'moduleName', 'moduleActive'));
    }

    /**
     * Включение модуля
     *
     * @param Request          $request
     * @param PhinxApplication $app
     * @return void
     */
    public function install(Request $request, PhinxApplication $app): void
    {
        $moduleName = check($request->input('module'));
        $modulePath = APP . '/Modules/' . $moduleName;

        if (! preg_match('|^[A-Z][\w\-]+$|', $moduleName) || ! file_exists($modulePath)) {
            abort('default', 'Данный модуль не найден!');
        }

        $module = include $modulePath . '/module.php';

        // Создание ссылок
        if (isset($module['symlinks'])) {
            foreach ($module['symlinks'] as $key => $symlink) {
                if (file_exists($symlink)) {
                    unlink($symlink);
                }

                symlink($modulePath . '/' . $key, $symlink);
            }
        }

        // Выполнение миграций
        if (file_exists($modulePath . '/migrations')) {
            $app->add(new Migrate());

            /** @var Migrate $command */
            $command = $app->find('migrate');

            $config = require APP . '/migration.php';
            $config['paths']['migrations'] = $modulePath . '/migrations';

            $command->setConfig(new Config($config));

            $wrap = new TextWrapper($app);
            $wrap->getMigrate();
        }

        if (file_exists(STORAGE . '/temp/routes.dat')) {
            unlink (STORAGE . '/temp/routes.dat');
        }

        $mod = Module::query()->firstOrNew(['name' => $moduleName]);

        if ($mod->exists) {
            $mod->update([
                'version'    => $module['version'],
                'updated_at' => SITETIME,
            ]);

            $result = 'Модуль успешно обновлен!';
        } else {
            $mod->fill([
                'version'    => $module['version'],
                'updated_at' => SITETIME,
                'created_at' => SITETIME,
            ])->save();

            $result = 'Модуль успешно включен!';
        }

        setFlash('success', $result);
        redirect('/admin/modules/module?module=' . $moduleName);
    }

    /**
     * Отключение модуля
     *
     * @param Request          $request
     * @param PhinxApplication $app
     * @return void
     */
    public function uninstall(Request $request, PhinxApplication $app): void
    {
        $moduleName = check($request->input('module'));
        $modulePath = APP . '/Modules/' . $moduleName;

        if (! preg_match('|^[A-Z][\w\-]+$|', $moduleName) || ! file_exists($modulePath)) {
            abort('default', 'Данный модуль не найден!');
        }

        $module = include $modulePath . '/module.php';

        // Удаление ссылок
        if (isset($module['symlinks'])) {
            foreach ($module['symlinks'] as $key => $symlink) {
                if (file_exists($symlink)) {
                    unlink($symlink);
                }
            }
        }

        // Откат миграций
        if (file_exists($modulePath . '/migrations')) {

            $app->add(new Migrate());

            /** @var Migrate $command */
            $command = $app->find('rollback');

            $config = require APP . '/migration.php';
            $config['paths']['migrations'] = $modulePath . '/migrations';

            $command->setConfig(new Config($config));

            $wrap = new TextWrapper($app);
            $wrap->getRollback(null, 0);
        }

        if (file_exists(STORAGE . '/temp/routes.dat')) {
            unlink (STORAGE . '/temp/routes.dat');
        }

        Module::query()->where('name', $moduleName)->delete();

        setFlash('success', 'Модуль успешно отключен!');
        redirect('/admin/modules/module?module=' . $moduleName);
    }
}
