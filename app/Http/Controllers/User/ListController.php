<?php

declare(strict_types=1);

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ListController extends Controller
{
    public function userlist(Request $request): View
    {
        // Значения уходят обратно в ссылки и поля формы: массив из ?type[]= ронял шаблон
        $type = in_array($request->input('type'), ['admins', 'birthdays'], true) ? $request->input('type') : 'users';
        $user = is_string($request->input('user')) ? $request->input('user') : '';

        [$sorting, $orderBy, $sort] = User::getSorting($request->input('sort', 'point'), $request->input('order'));
        $order = $orderBy[1];

        $users = User::query()
            ->when($type === 'admins', fn (Builder $q) => $q->whereIn('level', User::ADMIN_GROUPS))
            ->when($type === 'birthdays', fn (Builder $q) => $q->whereRaw('substr(birthday, 1, 5) = ?', now()->format('d.m')))
            ->when($user !== '', fn (Builder $q) => $q->where(
                fn (Builder $q) => $q->where('login', 'like', '%' . $user . '%')->orWhere('name', 'like', '%' . $user . '%')
            ))
            ->orderBy(...$orderBy)
            ->orderBy('id')
            ->paginate(setting('userlist'))
            ->appends($request->query());

        return view('users/users', compact('users', 'user', 'type', 'sort', 'order', 'sorting'));
    }
}
