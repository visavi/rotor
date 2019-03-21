<?php

declare(strict_types=1);

namespace App\Controllers\Admin;

use App\Models\User;

class BanlistController extends AdminController
{
    public function __construct()
    {
        parent::__construct();

        if (! isAdmin(User::MODER)) {
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
        $total = User::query()->where('level', User::BANNED)->where('timeban', '>', SITETIME)->count();
        $page = paginate(setting('reglist'), $total);

        $users = User::query()
            ->where('level', User::BANNED)
            ->where('timeban', '>', SITETIME)
            ->orderBy('timeban')
            ->limit($page->limit)
            ->offset($page->offset)
            ->with('lastBan')
            ->get();

        return view('admin/banlists/index', compact('users', 'page'));
    }
}
