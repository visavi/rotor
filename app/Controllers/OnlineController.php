<?php

namespace App\Controllers;

class OnlineController extends BaseController
{
    /**
     * Главная страница
     */
    public function index()
    {
        $total = Online::whereNotNull('user_id')->count();
        $all   = Online::count();

        $page = paginate(setting('onlinelist'), $total);

        $online = Online::whereNotNull('user_id')
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
        $total      = Online::count();
        $registered = Online::whereNotNull('user_id')->count();

        $page = paginate(setting('onlinelist'), $total);

        $online = Online::with('user')
            ->orderBy('updated_at', 'desc')
            ->offset($page['offset'])
            ->limit(setting('onlinelist'))
            ->get();

        return view('pages/online_all', compact('online', 'page', 'registered'));
    }
}
