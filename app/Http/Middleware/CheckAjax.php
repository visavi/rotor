<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class CheckAjax
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
        if (! $request->ajax()) {
            abort(403, __('validator.not_ajax'));
        }

        return $next($request);
    }
}
