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
        $modules = array_map('basename', glob(APP . '/Modules/*', GLOB_ONLYDIR));

        return view('admin/modules/index', compact('modules'));
    }

    /**
     * Просмотр модуля
     *
     * @param Request $request
     * @return string
     */
    public function module(Request $request): string
    {
        $module = check($request->input('module'));

        // TODO придумать какой-то конфиг, где будет указано название модуля, автор, описание, сайт автора, итд

        $modulePath = APP . '/Modules/' . $module;

        if (! preg_match('|^[\w\-]+$|i', $module) || ! file_exists($modulePath)) {
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

        return view('admin/modules/module', compact('module', 'migrations', 'images'));
    }
}
