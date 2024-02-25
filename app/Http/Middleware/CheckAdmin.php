<?php

namespace App\Http\Middleware;

use App\Models\Log;
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

        Log::query()->create([
            'user_id'    => getUser('id'),
            'request'    => utfSubstr($request->getRequestUri(), 0, 250),
            'referer'    => utfSubstr($request->header('referer'), 0, 250),
            'ip'         => getIp(),
            'brow'       => getBrowser(),
            'created_at' => SITETIME,
        ]);

        return $next($request);
    }
}
