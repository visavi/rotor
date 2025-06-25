<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;

class CheckUserState
{
    /**
     * Handle an incoming request.
     */
    public function handle(Request $request, Closure $next)
    {
        if (
            $request->routeIs('ipban')
            || (Route::has('install') && $request->is('install*'))
        ) {
            return $next($request);
        }

        if ($user = Auth::user()) {
            // Проверка бана
            if ($user->isBanned() && ! $request->routeIs('ban', 'rules', 'logout')) {
                return redirect('ban?user=' . $user->login);
            }

            // Проверка статуса pending
            if ($user->isPended() && ! $request->routeIs('verify', 'confirm', 'ban', 'logout', 'captcha')) {
                return redirect()->route('verify', ['user' => $user->login]);
            }

            // Обновление данных пользователя
            $user->updatePrivate();
            $user->gettingBonus();
        }

        return $next($request);
    }
}
