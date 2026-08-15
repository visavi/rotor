<?php

declare(strict_types=1);

namespace App\Traits;

use Illuminate\Http\Request;

/**
 * Разбор параметров постраничной выдачи API
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
}
