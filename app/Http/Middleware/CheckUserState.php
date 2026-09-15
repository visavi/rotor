<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class CheckUserState
{
    /**
     * Не пускает забаненных и неподтверждённых дальше служебных страниц
     */
    public function handle(Request $request, Closure $next)
    {
        $user = auth()->user();

        if (! $user) {
            return $next($request);
        }

        // Проверка бана
        if ($user->isBanned() && ! $request->routeIs('ban', 'rules', 'logout')) {
            return redirect('ban?user=' . $user->login);
        }

        // Проверка статуса pending
        if ($user->isPended() && ! $request->routeIs('verify', 'confirm', 'ban', 'logout', 'captcha')) {
            return redirect()->route('verify', ['user' => $user->login]);
        }

        return $next($request);
    }
}
