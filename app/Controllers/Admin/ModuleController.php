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

        $migrations = false;
        if (file_exists($modulePath . '/migrations')) {
            $migrations = true;
        }

        $images = false;
        if (file_exists($modulePath . '/images')) {
            $images = true;
        }

        $module = include $modulePath . '/module.php';

        return view('admin/modules/module', compact('module', 'moduleName', 'migrations', 'images'));
    }
}
