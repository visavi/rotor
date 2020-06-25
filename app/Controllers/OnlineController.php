<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Models\Online;

class OnlineController extends BaseController
{
    /**
     * Главная страница
     *
     * @return string
     */
    public function index(): string
    {
        $other  = Online::query()->count();
        $guests = false;

        $online = Online::query()
            ->whereNotNull('user_id')
            ->with('user')
            ->orderByDesc('updated_at')
            ->paginate(setting('onlinelist'));

        return view('pages/online', compact('online', 'other', 'guests'));
    }

    /**
     * Список всех пользователей
     *
     * @return string
     */
    public function all(): string
    {
        $other  = Online::query()->whereNotNull('user_id')->count();
        $guests = true;

        $online = Online::with('user')
            ->orderByDesc('updated_at')
            ->paginate(setting('onlinelist'));

        return view('pages/online', compact('online', 'other', 'guests'));
    }
}
