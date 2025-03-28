<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class CheckAdmin
{
    /**
     * Handle an incoming request.
     */
    public function handle(Request $request, Closure $next, ?string $level = null)
    {
        if (! isAdmin($level)) {
            abort(403, __('errors.forbidden'));
        }

        return $next($request);
    }
}
