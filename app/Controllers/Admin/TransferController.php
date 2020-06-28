<?php

declare(strict_types=1);

namespace App\Controllers\Admin;

use App\Models\Transfer;
use App\Models\User;
use Illuminate\Http\Request;

class TransferController extends AdminController
{
    /**
     * Конструктор
     */
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
        $transfers = Transfer::query()
            ->orderByDesc('created_at')
            ->with('user', 'recipientUser')
            ->paginate(setting('listtransfers'));

        return view('admin/transfers/index', compact('transfers'));
    }

    /**
     * Просмотр всех переводов
     *
     * @param Request $request
     *
     * @return string
     */
    public function view(Request $request): string
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
