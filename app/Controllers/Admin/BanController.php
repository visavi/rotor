<?php

namespace App\Controllers\Admin;

use App\Classes\Request;
use App\Models\Note;
use App\Models\User;

class BanController extends AdminController
{
    public function __construct()
    {
        parent::__construct();

        if (! isAdmin(User::MODER)) {
            abort('403', 'Доступ запрещен!');
        }
    }

    /**
     * Главная страница
     */
    public function index()
    {
        return view('admin/ban/index');
    }

    /**
     * Редактирование пользователя
     */
    public function edit()
    {
        $login = check(Request::input('user'));

        $user = User::query()->where('login', $login)->with('lastBan')->first();

        if (! $user) {
            abort('default', 'Пользователь не найден!');
        }
/*
        if (in_array($user->level, User::ADMIN_GROUPS)) {
            abort('default', 'Запрещено банить администрацию сайта!');
        }*/

        $note = Note::query()->where('user_id', $user->id)->first();

        return view('admin/ban/edit', compact('user', 'note'));
    }
}
