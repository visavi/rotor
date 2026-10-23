<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class CheckAdmin
{
    /**
     * Handle an incoming request.
     */
    public function handle(Request $request, Closure $next, ?string $level = null): Response
    {
        if (! auth()->check()) {
            return redirect()->route('login');
        }

        if (! isAdmin($level)) {
            abort(403, __('errors.forbidden'));
        }

        return $next($request);
    }
}
