<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Artisan;

class CheckInstallSite
{
    /**
     * Handle an incoming request.
     *
     * @param Request $request
     * @param Closure $next
     *
     * @return mixed
     */
    public function handle(Request $request, Closure $next)
    {
        if (! setting('app_installed')) {
            if (! config('app.key')) {
                Artisan::call('key:generate');
            }

            if (! $request->is('install*')) {
                return redirect('install');
            }
        }

        return $next($request);
    }
}
