<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Session\Middleware\StartSession;

class StartWebSession extends StartSession
{
    /**
     * Не открывает сессию там, где её некому хранить: клиент api ходит
     * по токену без cookie, а каждый старт оставлял файл в storage
     * и Set-Cookie на json-ответе. Стек при этом остаётся глобальным —
     * страница 404 для неизвестного URL рендерится до групповых middleware
     */
    public function handle($request, Closure $next)
    {
        if ($request->is('api/*', 'up')) {
            return $next($request);
        }

        return parent::handle($request, $next);
    }
}
