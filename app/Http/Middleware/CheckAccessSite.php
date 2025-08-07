<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class CheckAccessSite
{
    /**
     * Handle an incoming request.
     */
    public function handle(Request $request, Closure $next)
    {
        // Сайт закрыт для гостей
        if (
            setting('closedsite') === 1
            && ! $request->is('login', 'register', 'recovery', 'captcha')
            && auth()->guest()
        ) {
            return redirect()->route('login')->with('danger', __('main.not_authorized'));
        }

        // Сайт закрыт для всех
        if (
            setting('closedsite') === 2
            && ! isAdmin()
            && ! $request->is('login', 'captcha', 'closed')
        ) {
            return redirect('closed');
        }

        return $next($request);
    }
}
