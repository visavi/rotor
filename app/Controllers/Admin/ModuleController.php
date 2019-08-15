<?php

declare(strict_types=1);

namespace App\Controllers\Admin;

use App\Models\Module;
use App\Models\User;
use Exception;
use Illuminate\Http\Request;

class ModuleController extends AdminController
{
    /**
     * ModuleController constructor.
     */
    public function __construct()
    {
        parent::__construct();

        if (! isAdmin(User::BOSS)) {
            abort(403, trans('errors.forbidden'));
        }
    }

    /**
     * Главная страница
     *
     * @return string
     */
    public function index(): string
    {
        $modules = Module::query()->get();
        $moduleInstall = [];
        foreach ($modules as $module) {
            $moduleInstall[$module->name] = $module;
        }

        $moduleNames = [];
        $modulesLoaded = glob(MODULES . '/*', GLOB_ONLYDIR);
        foreach ($modulesLoaded as $module) {
            if (file_exists($module . '/module.php')) {
                $moduleNames[basename($module)] = include $module . '/module.php';
            }
        }

        return view('admin/modules/index', compact('moduleInstall', 'moduleNames'));
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
        $modulePath = MODULES . '/' . $moduleName;

        if (! preg_match('|^[A-Z][\w\-]+$|', $moduleName) || ! file_exists($modulePath)) {
            abort('default', 'Данный модуль не найден!');
        }

        $moduleConfig = include $modulePath . '/module.php';

        if (file_exists($modulePath . '/screenshots')) {
            $moduleConfig['screenshots'] = glob($modulePath . '/screenshots/*.{gif,png,jpg,jpeg}', GLOB_BRACE);
        }

        if (file_exists($modulePath . '/migrations')) {
            $moduleConfig['migrations'] = array_map('basename', glob($modulePath . '/migrations/*.php'));
        }

        if (file_exists($modulePath . '/resources/assets')) {
            $moduleConfig['symlink'] = str_replace(HOME, '', (new Module())->getLinkName($modulePath));
        }

        $module = Module::query()->where('name', $moduleName)->first();

        return view('admin/modules/module', compact('module', 'moduleConfig', 'moduleName'));
    }

    /**
     * Установка модуля
     *
     * @param Request $request
     * @return void
     */
    public function install(Request $request): void
    {
        $moduleName = check($request->input('module'));
        $enable     = int($request->input('enable'));
        $update     = int($request->input('update'));
        $modulePath = MODULES . '/' . $moduleName;

        if (! preg_match('|^[A-Z][\w\-]+$|', $moduleName) || ! file_exists($modulePath)) {
            abort('default', 'Данный модуль не найден!');
        }

        /** @var Module $module */
        $module = Module::query()->firstOrNew(['name' => $moduleName]);

        $moduleConfig = include $modulePath . '/module.php';
        $module->createSymlink($modulePath);
        $module->migrate($modulePath);
        clearCache(['routes']);

        $result = 'Модуль успешно установлен!';

        if ($module->exists) {
            if ($update) {
                $module->update([
                    'version'    => $moduleConfig['version'],
                    'updated_at' => SITETIME,
                ]);
                $result = 'Модуль успешно обновлен!';
            }

            if ($enable) {
                $module->update([
                    'disabled' => 0,
                    'updated_at' => SITETIME,
                ]);
                $result = 'Модуль успешно включен!';
            }
        } else {
            $module->fill([
                'version'    => $moduleConfig['version'],
                'updated_at' => SITETIME,
                'created_at' => SITETIME,
            ])->save();
        }

        setFlash('success', $result);
        redirect('/admin/modules/module?module=' . $moduleName);
    }

    /**
     * Удаление/Выключение модуля
     *
     * @param Request $request
     * @return void
     * @throws Exception
     */
    public function uninstall(Request $request): void
    {
        $moduleName = check($request->input('module'));
        $disable    = int($request->input('disable'));
        $modulePath = MODULES . '/' . $moduleName;

        if (! preg_match('|^[A-Z][\w\-]+$|', $moduleName) || ! file_exists($modulePath)) {
            abort('default', 'Данный модуль не найден!');
        }

        /** @var Module $module */
        $module = Module::query()->where('name', $moduleName)->first();
        if (! $module) {
            abort('default', 'Данный модуль не найден!');
        }

        $module->deleteSymlink($modulePath);
        clearCache(['routes']);

        if ($disable) {
            $module->update([
                'disabled' => 1,
                'updated_at' => SITETIME,
            ]);
            $result = 'Модуль успешно выключен!';
        } else {
            $module->rollback($modulePath);
            $module->delete();
            $result = 'Модуль успешно удален!';
        }

        setFlash('success', $result);
        redirect('/admin/modules/module?module=' . $moduleName);
    }
}
