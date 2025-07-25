<?php

declare(strict_types=1);

namespace App\Http\Controllers\Admin;

use App\Models\Module;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Artisan;
use Illuminate\View\View;

class ModuleController extends AdminController
{
    /**
     * Главная страница
     */
    public function index(): View
    {
        $modules = Module::query()->get();
        $moduleInstall = [];
        foreach ($modules as $module) {
            $moduleInstall[$module->name] = $module;
        }

        $moduleNames = [];
        $modulesLoaded = glob(base_path('modules/*'), GLOB_ONLYDIR);
        foreach ($modulesLoaded as $module) {
            if (file_exists($module . '/module.php')) {
                $moduleNames[basename($module)] = include $module . '/module.php';
            }
        }

        return view('admin/modules/index', compact('moduleInstall', 'moduleNames'));
    }

    /**
     * Просмотр модуля
     */
    public function module(Request $request): View
    {
        $moduleName = $request->input('module');
        $modulePath = base_path('modules/' . $moduleName);

        if (! preg_match('|^[A-Z][\w\-]+$|', $moduleName) || ! file_exists($modulePath)) {
            abort(200, __('admin.modules.module_not_found'));
        }

        $moduleConfig = include $modulePath . '/module.php';

        if (file_exists($modulePath . '/screenshots')) {
            $moduleConfig['screenshots'] = glob($modulePath . '/screenshots/*.{gif,png,jpg,jpeg,webp}', GLOB_BRACE);
        }

        if (file_exists($modulePath . '/migrations')) {
            $moduleConfig['migrations'] = array_map('basename', glob($modulePath . '/migrations/*.php'));
        }

        if (file_exists($modulePath . '/resources/assets')) {
            $moduleConfig['symlink'] = Module::getLinkNameByPath($modulePath);
        }

        if (file_exists($modulePath . '/config.php')) {
            $moduleConfig['config'] = file_get_contents($modulePath . '/config.php');
        }

        if (file_exists($modulePath . '/routes.php')) {
            $moduleConfig['routes'] = file_get_contents($modulePath . '/routes.php');
        }

        if (file_exists($modulePath . '/hooks.php')) {
            $moduleConfig['hooks'] = file_get_contents($modulePath . '/hooks.php');
        }

        if (file_exists($modulePath . '/middleware.php')) {
            $moduleConfig['middleware'] = file_get_contents($modulePath . '/middleware.php');
        }

        $module = Module::query()->where('name', $moduleName)->first();

        return view('admin/modules/module', compact('module', 'moduleConfig', 'moduleName'));
    }

    /**
     * Установка модуля
     */
    public function install(Request $request): RedirectResponse
    {
        $moduleName = $request->input('module');
        $enable = int($request->input('enable'));
        $update = int($request->input('update'));
        $modulePath = base_path('modules/' . $moduleName);

        if (! preg_match('|^[A-Z][\w\-]+$|', $moduleName) || ! file_exists($modulePath)) {
            abort(200, __('admin.modules.module_not_found'));
        }

        $module = Module::query()->firstOrNew(['name' => $moduleName]);

        $moduleConfig = include $modulePath . '/module.php';
        $module->createSymlink();
        $module->migrate();

        Artisan::call('route:clear');
        $result = __('admin.modules.module_success_installed');

        if ($module->exists) {
            if ($update) {
                $module->update([
                    'version'    => $moduleConfig['version'],
                    'updated_at' => SITETIME,
                ]);
                $result = __('admin.modules.module_success_updated');
            }

            if ($enable) {
                $module->update([
                    'active'     => true,
                    'updated_at' => SITETIME,
                ]);
                $result = __('admin.modules.module_success_enabled');
            }
        } else {
            $module->fill([
                'version'    => $moduleConfig['version'],
                'updated_at' => SITETIME,
                'created_at' => SITETIME,
            ])->save();
        }

        clearCache('modules');
        setFlash('success', $result);

        return redirect('admin/modules/module?module=' . $moduleName);
    }

    /**
     * Удаление/Выключение модуля
     */
    public function uninstall(Request $request): RedirectResponse
    {
        $moduleName = $request->input('module');
        $disable = int($request->input('disable'));
        $modulePath = base_path('modules/' . $moduleName);

        if (! preg_match('|^[A-Z][\w\-]+$|', $moduleName) || ! file_exists($modulePath)) {
            abort(200, __('admin.modules.module_not_found'));
        }

        $module = Module::query()->where('name', $moduleName)->first();
        if (! $module) {
            abort(200, __('admin.modules.module_not_found'));
        }

        $module->deleteSymlink();
        Artisan::call('route:clear');

        if ($disable) {
            $module->update([
                'active'     => false,
                'updated_at' => SITETIME,
            ]);
            $result = __('admin.modules.module_success_disabled');
        } else {
            $module->rollback();
            $module->delete();
            $result = __('admin.modules.module_success_deleted');
        }

        clearCache('modules');
        setFlash('success', $result);

        return redirect('admin/modules/module?module=' . $moduleName);
    }
}
