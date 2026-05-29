<?php

namespace App\Http\Middleware;

use App\Classes\Metrika;
use Closure;
use Illuminate\Http\Request;

class SaveStatistic
{
    /**
     * Handle an incoming request.
     */
    public function handle(Request $request, Closure $next)
    {
        if ($request->isMethod('GET') && ! $request->ajax() && ! $request->expectsJson()) {
            (new Metrika())->saveStatistic();
        }

        return $next($request);
    }
}
