<?php

namespace App\Controllers\Admin;

use App\Classes\Request;
use App\Models\Transfer;

class TransferController extends AdminController
{
    /**
     * Главная страница
     */
    public function index()
    {
        $total = Transfer::query()->count();
        $page = paginate(setting('listtransfers'), $total);

        $transfers = Transfer::query()
            ->orderBy('created_at', 'desc')
            ->limit($page->limit)
            ->offset($page->offset)
            ->with('user', 'recipientUser')
            ->get();

        return view('admin/transfer/index', compact('transfers', 'page'));
    }

    /**
     * Просмотр всех переводов
     */
    public function view()
    {
        $login = check(Request::input('user'));

        if (! $user = getUserByLogin($login)) {
            abort(404, 'Пользователь с данным логином не найден!');
        }

        $total = Transfer::query()->where('user_id', $user->id)->count();
        $page = paginate(setting('listtransfers'), $total);

        $transfers = Transfer::query()
            ->where('user_id', $user->id)
            ->orderBy('created_at', 'desc')
            ->limit($page->limit)
            ->offset($page->offset)
            ->with('user', 'recipientUser')
            ->get();

        return view('admin/transfer/view', compact('transfers', 'page', 'user'));
    }
}
