<?php

namespace App\Http\Middleware;

use App\Support\Registry;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class AdminLogger
{
    /**
     * Handle an incoming request.
     */
    public function handle(Request $request, Closure $next): Response
    {
        return $next($request);
    }

    /**
     * Handle tasks after the response has been sent to the browser.
     */
    public function terminate(Request $request, Response $response): void
    {
        if (auth()->check()) {
            foreach (Registry::$onAdminLog as $handler) {
                $handler($request);
            }
        }
    }
}
