<?php

declare(strict_types=1);

namespace App\Traits;

use App\Models\User;
use Illuminate\Http\Request;

/**
 * Разбор параметров списков API: страница, порядок, автор
 */
trait HandlesApiPagination
{
    /**
     * Количество элементов на страницу
     */
    protected function apiPerPage(Request $request, int $default = 10): int
    {
        return max(1, min($request->integer('per_page', $default), 100));
    }

    /**
     * Направление сортировки
     */
    protected function apiOrder(Request $request, string $default = 'asc'): string
    {
        $order = $request->input('order', $default);

        return in_array($order, ['asc', 'desc'], true) ? $order : $default;
    }

    /**
     * Автор из ?user= — списки «записи пользователя»; неизвестный логин — 404
     */
    protected function apiUser(Request $request): ?User
    {
        if (! $request->filled('user')) {
            return null;
        }

        $user = getUserByLogin($request->string('user')->value());

        if (! $user) {
            abort(404, __('validator.user'));
        }

        return $user;
    }
}
