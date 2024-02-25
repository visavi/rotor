<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class CheckUser
{
    /**
     * Handle an incoming request.
     */
    public function handle(Request $request, Closure $next)
    {
        if (! getUser()) {
            abort(403, __('main.not_authorized'));
        }

        return $next($request);
    }
}
