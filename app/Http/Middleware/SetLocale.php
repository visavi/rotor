<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\App;
use Throwable;

class SetLocale
{
    /**
     * Выбирает язык: выбор в сессии, затем язык профиля, затем язык сайта
     */
    public function handle(Request $request, Closure $next)
    {
        try {
            $user = auth()->user();
        } catch (Throwable) {
            // На неустановленном сайте таблицы пользователей ещё нет
            $user = null;
        }

        $language = $user->language ?? setting('language', config('app.locale'));

        // На api сессия не стартует, язык берётся из профиля
        if ($request->hasSession() && $request->session()->has('language')) {
            $language = $request->session()->get('language');
        }

        self::apply($language);

        return $next($request);
    }

    /**
     * Ставит язык, откатываясь на язык сайта, если каталога переводов нет
     */
    public static function apply(?string $language): void
    {
        if (! $language || ! file_exists(resource_path('lang/' . $language))) {
            $language = setting('language', config('app.locale'));
        }

        App::setLocale($language);
    }
}
