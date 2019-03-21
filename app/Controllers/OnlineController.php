<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Models\Online;

class OnlineController extends BaseController
{
    /**
     * Главная страница
     */
    public function index()
    {
        $total  = Online::query()->whereNotNull('user_id')->count();
        $all    = Online::query()->count();
        $guests = false;

        $page = paginate(setting('onlinelist'), $total);

        $online = Online::query()
            ->whereNotNull('user_id')
            ->with('user')
            ->orderBy('updated_at', 'desc')
            ->offset($page->offset)
            ->limit($page->limit)
            ->get();

        return view('pages/online', compact('online', 'page', 'total', 'all', 'guests'));
    }

    /**
     * Список всех пользователей
     */
    public function all()
    {
        $all    = Online::query()->count();
        $total  = Online::query()->whereNotNull('user_id')->count();
        $guests = true;

        $page = paginate(setting('onlinelist'), $all);

        $online = Online::with('user')
            ->orderBy('updated_at', 'desc')
            ->offset($page->offset)
            ->limit($page->limit)
            ->get();

        return view('pages/online', compact('online', 'page', 'total', 'all', 'guests'));
    }
}
