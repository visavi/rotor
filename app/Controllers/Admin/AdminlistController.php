<?php

namespace App\Controllers\Admin;

use App\Models\User;

class AdminlistController extends AdminController
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
        $users = User::query()
            ->whereIn('level', User::ADMIN_GROUPS)
            ->orderByRaw("field(level, '".implode("','", User::ADMIN_GROUPS)."')")
            ->get();

        return view('admin/administrators/index', compact('users'));
    }
}
