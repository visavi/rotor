<?php

declare(strict_types=1);

namespace App\Controllers\Admin;

use App\Models\Module;
use App\Models\User;
use Illuminate\Http\Request;

use Phinx\Console\PhinxApplication;


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

        $module = Module::query()->where('name', $moduleName)->first();

        $moduleConfig = include $modulePath . '/module.php';

        if (file_exists($modulePath . '/screenshots')) {
            $moduleConfig['screenshots'] = glob($modulePath . '/screenshots/*.{gif,png,jpg,jpeg}', GLOB_BRACE);
        }

        if (file_exists($modulePath . '/migrations')) {
            $moduleConfig['migrations'] = array_map('basename', glob($modulePath . '/migrations/*.php'));
        }

        if ($moduleConfig['symlinks']) {
            $moduleConfig['symlinks'] = array_map(static function ($symlink) {
                return str_replace(HOME, '', $symlink);
            }, $moduleConfig['symlinks']);
        }

        return view('admin/modules/module', compact('module', 'moduleConfig', 'moduleName'));
    }

    /**
     * Активация модуля
     *
     * @param Request $request
     * @return void
     */
    public function install(Request $request): void
    {
        $moduleName = check($request->input('module'));
        $modulePath = APP . '/Modules/' . $moduleName;

        if (! preg_match('|^[A-Z][\w\-]+$|', $moduleName) || ! file_exists($modulePath)) {
            abort('default', 'Данный модуль не найден!');
        }

        /** @var Module $module */
        $module = Module::query()->firstOrNew(['name' => $moduleName]);

        $moduleConfig = include $modulePath . '/module.php';
        $module->migrate($modulePath . '/migrations');
        $module->createSymlinks($modulePath, $moduleConfig);
        clearCache('routes');

        if ($module->exists) {
            $result = $module->disabled ? 'Модуль успешно включен!' : 'Модуль успешно обновлен!';

            $module->update([
                'disabled'   => 0,
                'version'    => $moduleConfig['version'],
                'updated_at' => SITETIME,
            ]);
        } else {
            $module->fill([
                'version'    => $moduleConfig['version'],
                'updated_at' => SITETIME,
                'created_at' => SITETIME,
            ])->save();

            $result = 'Модуль успешно активирован!';
        }

        setFlash('success', $result);
        redirect('/admin/modules/module?module=' . $moduleName);
    }

    /**
     * Деактивация/Выключение модуля
     *
     * @param Request $request
     * @return void
     * @throws \Exception
     */
    public function uninstall(Request $request): void
    {
        $moduleName = check($request->input('module'));
        $disable    = int($request->input('disable'));
        $modulePath = APP . '/Modules/' . $moduleName;

        if (! preg_match('|^[A-Z][\w\-]+$|', $moduleName) || ! file_exists($modulePath)) {
            abort('default', 'Данный модуль не найден!');
        }

        /** @var Module $module */
        $module = Module::query()->where('name', $moduleName)->first();
        if (! $module) {
            abort('default', 'Данный модуль не найден!');
        }

        $moduleConfig = include $modulePath . '/module.php';

        $module->rollback($modulePath . '/migrations');
        $module->deleteSymlinks($moduleConfig);
        clearCache('routes');

        if ($disable) {
            $module->update(['disabled' => 1]);
            $result = 'Модуль успешно отключен!';
        } else {
            $module->delete();
            $result = 'Модуль успешно деактивирован!';
        }

        setFlash('success', $result);
        redirect('/admin/modules/module?module=' . $moduleName);
    }
}
