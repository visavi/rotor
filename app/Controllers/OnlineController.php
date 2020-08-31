<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Models\Online;
use Illuminate\Database\Query\JoinClause;

class OnlineController extends BaseController
{
    /**
     * Главная страница
     *
     * @return string
     */
    public function index(): string
    {
        $guests = false;

        $online = Online::query()
            ->select('o1.*')
            ->from('online as o1')
            ->leftJoin('online as o2', static function (JoinClause $join) {
                $join->on('o1.user_id', 'o2.user_id')
                    ->on('o1.updated_at', '<', 'o2.updated_at');
            })
            ->whereNull('o2.updated_at')
            ->whereNotNull('o1.user_id')
            ->with('user')
            ->orderByDesc('updated_at')
            ->paginate(setting('onlinelist'));

        return view('pages/online', compact('online', 'guests'));
    }

    /**
     * Список всех пользователей
     *
     * @return string
     */
    public function all(): string
    {
        $guests = true;

        $online = Online::query()
            ->select('o1.*')
            ->from('online as o1')
            ->leftJoin('online as o2', static function (JoinClause $join) {
                $join->on('o1.user_id', 'o2.user_id')
                    ->on('o1.updated_at', '<', 'o2.updated_at');
            })
            ->whereNull('o2.updated_at')
            ->with('user')
            ->orderByDesc('updated_at')
            ->paginate(setting('onlinelist'));

        return view('pages/online', compact('online', 'guests'));
    }
}
