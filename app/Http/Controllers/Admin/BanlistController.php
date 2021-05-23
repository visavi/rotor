<?php

declare(strict_types=1);

namespace App\Http\Controllers\Admin;

use App\Models\User;
use Illuminate\View\View;

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
     * @return View
     */
    public function index(): View
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
