<?php

namespace App\Http\Middleware;

use App\Support\Locale;
use Closure;
use Illuminate\Http\Request;
use Throwable;

class SetLocale
{
    /**
     * Язык посетителя: явный выбор на сайте, затем профиль, затем язык браузера.
     * Без всего этого — язык сайта из настроек
     */
    public function handle(Request $request, Closure $next)
    {
        try {
            $user = auth()->user();
        } catch (Throwable) {
            // На неустановленном сайте таблицы пользователей ещё нет
            $user = null;
        }

        $language = Locale::fromRequest($request);

        // Клиент api без сессии: ему остаются профиль и заголовок
        if ($request->hasSession() && $request->session()->has('language')) {
            $language = $request->session()->get('language');
        } elseif ($user) {
            $language = $user->language;
        }

        Locale::apply($language);

        return $next($request);
    }
}
