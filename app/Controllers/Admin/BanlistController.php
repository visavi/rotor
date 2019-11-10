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
            abort(403, __('errors.forbidden'));
        }
    }

    /**
     * Главная страница
     *
     * @return string
     */
    public function index(): string
    {
        $users = User::query()
            ->where('level', User::BANNED)
            ->where('timeban', '>', SITETIME)
            ->orderBy('timeban')
            ->with('lastBan')
            ->paginate(setting('reglist'));

        return view('admin/banlists/index', compact('users'));
    }
}
