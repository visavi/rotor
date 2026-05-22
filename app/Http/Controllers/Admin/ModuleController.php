<?php

declare(strict_types=1);

namespace App\Http\Controllers\Admin;

use App\Models\Module;
use App\Models\ModuleRegistry;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Http;
use Illuminate\View\View;
use ZipArchive;

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

        $installed = array_intersect_key($moduleInstall, $moduleNames);
        $counts = [
            'all'           => count($moduleNames),
            'installed'     => count(array_filter($installed, fn ($m) => $m->active)),
            'disabled'      => count(array_filter($installed, fn ($m) => ! $m->active)),
            'not-installed' => count($moduleNames) - count($installed),
        ];

        return view('admin/modules/index', compact('moduleInstall', 'moduleNames', 'counts'));
    }

    /**
     * Просмотр модуля
     */
    public function module(Request $request): View
    {
        $moduleName = (string) $request->input('module');
        $modulePath = base_path('modules/' . $moduleName);

        if (! preg_match('|^[A-Z][\w\-]+$|', $moduleName) || ! file_exists($modulePath)) {
            abort(200, __('admin.modules.module_not_found'));
        }

        $moduleConfig = include $modulePath . '/module.php';
        $module = Module::query()->where('name', $moduleName)->first();

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

        if ($module && $module->settings) {
            $moduleConfig['settings'] = var_export($module->settings, true);
        }

        if (file_exists($modulePath . '/routes.php')) {
            $moduleConfig['routes'] = file_get_contents($modulePath . '/routes.php');
        }

        if (file_exists($modulePath . '/hooks.php')) {
            $moduleConfig['hooks'] = file_get_contents($modulePath . '/hooks.php');
        }

        if (file_exists($modulePath . '/helpers.php')) {
            $moduleConfig['helpers'] = file_get_contents($modulePath . '/helpers.php');
        }

        if (file_exists($modulePath . '/middleware.php')) {
            $moduleConfig['middleware'] = file_get_contents($modulePath . '/middleware.php');
        }

        $registryInfo = ModuleRegistry::getAvailableModules()[$moduleName] ?? null;

        return view('admin/modules/module', compact('module', 'moduleConfig', 'moduleName', 'registryInfo'));
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
     * Каталог модулей из реестров
     */
    public function marketplace(Request $request): View
    {
        $force = (bool) $request->input('refresh');
        $available = ModuleRegistry::getAvailableModules($force);

        $modules = Module::query()->get()->keyBy('name');
        $moduleNames = [];

        $modulesLoaded = glob(base_path('modules/*'), GLOB_ONLYDIR);
        foreach ($modulesLoaded as $module) {
            $moduleNames[] = basename($module);
        }

        return view('admin/modules/marketplace', compact('available', 'modules', 'moduleNames'));
    }

    /**
     * Форма загрузки модуля
     */
    public function upload(): View
    {
        return view('admin/modules/upload');
    }

    /**
     * Установка модуля из ZIP-файла
     */
    public function uploadZip(Request $request): RedirectResponse
    {
        if (! $request->hasFile('zip') || ! $request->file('zip')->isValid()) {
            setFlash('danger', __('admin.modules.upload_invalid_file'));

            return redirect()->route('admin.modules.upload');
        }

        try {
            $moduleName = $this->extractZip($request->file('zip')->getPathname());
        } catch (\Exception $e) {
            setFlash('danger', $e->getMessage());

            return redirect()->route('admin.modules.upload');
        }

        return redirect('/admin/modules/module?module=' . $moduleName)
            ->with('success', __('admin.modules.upload_success_extracted'));
    }

    /**
     * Установка модуля по URL
     */
    public function download(Request $request): RedirectResponse
    {
        $url = trim($request->input('url', ''));

        if (! filter_var($url, FILTER_VALIDATE_URL)) {
            setFlash('danger', __('admin.modules.download_invalid_url'));

            return redirect()->route('admin.modules.upload');
        }

        try {
            $response = Http::timeout(30)->get($url);

            if (! $response->ok()) {
                setFlash('danger', __('admin.modules.download_failed'));

                return redirect()->route('admin.modules.upload');
            }

            $tempDir = storage_path('app/temp');
            if (! is_dir($tempDir)) {
                mkdir($tempDir, 0755, true);
            }
            $tempFile = $tempDir . '/rotor_module_' . uniqid() . '.zip';
            file_put_contents($tempFile, $response->body());

            $moduleName = $this->extractZip($tempFile);
            @unlink($tempFile);
        } catch (\Exception $e) {
            setFlash('danger', $e->getMessage());

            return redirect()->route('admin.modules.upload');
        }

        return redirect('/admin/modules/module?module=' . $moduleName)
            ->with('success', __('admin.modules.upload_success_extracted'));
    }

    /**
     * Распаковка ZIP-архива модуля
     */
    private function extractZip(string $zipPath): string
    {
        $zip = new ZipArchive();

        if ($zip->open($zipPath) !== true) {
            throw new \RuntimeException(__('admin.modules.zip_open_failed'));
        }

        // Определяем корневую директорию модуля в архиве
        $topDirs = [];
        for ($i = 0; $i < $zip->numFiles; $i++) {
            $name = $zip->getNameIndex($i);

            if (str_contains($name, '..')) {
                $zip->close();
                throw new \RuntimeException(__('admin.modules.zip_invalid_path'));
            }

            $parts = explode('/', $name);
            if ($parts[0] !== '') {
                $topDirs[$parts[0]] = true;
            }
        }

        if (count($topDirs) !== 1) {
            $zip->close();
            throw new \RuntimeException(__('admin.modules.zip_invalid_structure'));
        }

        $moduleName = array_key_first($topDirs);

        if (! preg_match('/^[A-Z][A-Za-z0-9]+$/', $moduleName)) {
            $zip->close();
            throw new \RuntimeException(__('admin.modules.zip_invalid_name'));
        }

        $targetPath = base_path('modules/' . $moduleName);

        if (file_exists($targetPath) && ! is_link($targetPath)) {
            $zip->close();
            throw new \RuntimeException(__('admin.modules.module_dir_exists'));
        }

        $zip->extractTo(base_path('modules/'));
        $zip->close();

        $this->chmodRecursive($targetPath);

        if (! file_exists($targetPath . '/module.php')) {
            // Откат: удалить распакованное
            $this->deleteDirectory($targetPath);
            throw new \RuntimeException(__('admin.modules.zip_no_module_file'));
        }

        return $moduleName;
    }

    private function chmodRecursive(string $path): void
    {
        chmod($path, 0755);

        foreach (scandir($path) as $item) {
            if ($item === '.' || $item === '..') {
                continue;
            }

            $full = $path . '/' . $item;
            if (is_dir($full)) {
                $this->chmodRecursive($full);
            } else {
                chmod($full, 0644);
            }
        }
    }

    private function deleteDirectory(string $path): void
    {
        if (! is_dir($path)) {
            return;
        }

        foreach (scandir($path) as $item) {
            if ($item === '.' || $item === '..') {
                continue;
            }

            $full = $path . '/' . $item;
            is_dir($full) ? $this->deleteDirectory($full) : unlink($full);
        }

        rmdir($path);
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
