<?php

declare(strict_types=1);

namespace App\Http\Controllers\Admin;

use App\Models\Transfer;
use Illuminate\Http\Request;
use Illuminate\View\View;

class TransferController extends AdminController
{
    /**
     * Главная страница
     */
    public function index(): View
    {
        $transfers = Transfer::query()
            ->orderByDesc('created_at')
            ->with('user', 'recipientUser')
            ->paginate(setting('listtransfers'));

        return view('admin/transfers/index', compact('transfers'));
    }

    /**
     * Просмотр всех переводов
     */
    public function view(Request $request): View
    {
        if (! $user = getUserByLogin($request->input('user'))) {
            abort(404, __('validator.user'));
        }

        $transfers = Transfer::query()
            ->where('user_id', $user->id)
            ->orderByDesc('created_at')
            ->with('user', 'recipientUser')
            ->paginate(setting('listtransfers'))
            ->appends(['user' => $user->login]);

        return view('admin/transfers/view', compact('transfers', 'user'));
    }
}
