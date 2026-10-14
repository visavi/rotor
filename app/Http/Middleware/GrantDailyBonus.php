<?php

namespace App\Http\Middleware;

use App\Services\MetrikaService;
use Closure;
use Illuminate\Http\Request;

class GrantDailyBonus
{
    /**
     * Начисляет ежедневный бонус и показывает уведомление о нём
     */
    public function handle(Request $request, Closure $next)
    {
        // Бонус — при открытии страницы: у формы ответ редирект, у ajax — JSON,
        // уведомление там некому показать. Начислится на следующей странице
        if (! $request->isMethod('GET') || $request->ajax() || MetrikaService::isBackground($request)) {
            return $next($request);
        }

        if ($bonus = auth()->user()?->gettingBonus()) {
            // now(), а не flash(): только эта страница, на следующей не повторится.
            // Сообщение контроллера с прошлого редиректа остаётся рядом
            session()->now('success', [...(array) session('success', []), $bonus]);
        }

        return $next($request);
    }
}
