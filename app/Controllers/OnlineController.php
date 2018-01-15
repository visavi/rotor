<?php

namespace App\Controllers;

use App\Models\Online;

class OnlineController extends BaseController
{
    /**
     * Главная страница
     */
    public function index()
    {
        $total = Online::query()->whereNotNull('user_id')->count();
        $all   = Online::query()->count();

        $page = paginate(setting('onlinelist'), $total);

        $online = Online::query()
            ->whereNotNull('user_id')
            ->with('user')
            ->orderBy('updated_at', 'desc')
            ->offset($page['offset'])
            ->limit(setting('onlinelist'))
            ->get();

        return view('pages/online', compact('online', 'page', 'all'));
    }

    /**
     * Список всех пользователей
     */
    public function all()
    {
        $total      = Online::query()->count();
        $registered = Online::query()->whereNotNull('user_id')->count();

        $page = paginate(setting('onlinelist'), $total);

        $online = Online::with('user')
            ->orderBy('updated_at', 'desc')
            ->offset($page['offset'])
            ->limit($page['limit'])
            ->get();

        return view('pages/online_all', compact('online', 'page', 'registered'));
    }
}
