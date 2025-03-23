<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class CheckInstallSite
{
    /**
     * Handle an incoming request.
     */
    public function handle(Request $request, Closure $next)
    {

       // dd($request);

        if (! setting('app_installed') && ! $request->is('install*')) {
            return redirect('install');
        }

        return $next($request);
    }
}
