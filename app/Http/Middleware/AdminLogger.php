<?php

namespace App\Http\Middleware;

use App\Models\Log;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
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
            Log::query()->create([
                'user_id'    => auth()->id(),
                'request'    => Str::substr($request->getRequestUri(), 0, 250),
                'referer'    => Str::substr($request->header('referer'), 0, 250),
                'ip'         => getIp(),
                'brow'       => getBrowser(),
                'created_at' => SITETIME,
            ]);
        }
    }
}
