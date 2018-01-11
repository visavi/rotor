<?php

namespace App\Controllers\Admin;

use App\Classes\Request;
use App\Classes\Validator;
use App\Models\User;

class UserController extends AdminController
{
    /**
     * Конструктор
     */
    public function __construct()
    {
        parent::__construct();

        if (! isAdmin(User::ADMIN)) {
            abort(403, 'Доступ запрещен!');
        }
    }

    /**
     * Главная страница
     */
    public function index()
    {
        $total = User::query()->count();
        $page = paginate(setting('userlist'), $total);

        $users = User::query()
            ->orderBy('joined', 'desc')
            ->offset($page['offset'])
            ->limit($page['limit'])
            ->get();

        return view('admin/users/index', compact('users', 'page'));
    }
}
