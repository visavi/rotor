<?php

namespace App\Controllers\Admin;

use App\Classes\Validator;
use App\Models\Comment;
use App\Models\Guestbook;
use App\Models\Message;
use App\Models\Post;
use App\Models\Spam;
use App\Models\User;
use App\Models\Wall;
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
            abort('403', 'Доступ запрещен!');
        }
    }

    /**
     * Главная страница
     *
     * @return string
     */
    public function index(): string
    {
        $modules = glob(APP . '/Modules/*', GLOB_ONLYDIR);

        $moduleNames = [];
        foreach ($modules as $module) {
            $moduleNames[basename($module)] = include $module . '/module.php';
        }

        return view('admin/modules/index', compact('moduleNames'));
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

        if (! preg_match('|^[\w\-]+$|i', $moduleName) || ! file_exists($modulePath)) {
            abort('default', 'Данный модуль не найден!');
        }

        $module = include $modulePath . '/module.php';

        $migrations = false;
        if (file_exists($modulePath . '/migrations')) {
            $migrations = array_map('basename', glob($modulePath . '/migrations/*.php'));
        }

        // Проверить существование симлинков, права на директрию

        return view('admin/modules/module', compact('module', 'moduleName', 'migrations'));
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
        $modulePath = APP . '/Modules/' . $moduleName;

        if (! preg_match('|^[\w\-]+$|i', $moduleName) || ! file_exists($modulePath)) {
            abort('default', 'Данный модуль не найден!');
        }

        $module = include $modulePath . '/module.php';

        foreach ($module['symlinks'] as $key => $symlink) {
            if (! file_exists($symlink)) {
                symlink($modulePath . '/' . $key, $symlink);
            }
        }

        setFlash('success', 'Модуль успешно установлен!');
        redirect('/admin/modules/module?module=' . $moduleName);
    }
}
