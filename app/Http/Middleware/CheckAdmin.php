<?php

namespace App\Http\Middleware;

use App\Models\Log;
use Closure;
use Illuminate\Http\Request;

class CheckAdmin
{
    /**
     * Handle an incoming request.
     *
     * @param Request     $request
     * @param Closure     $next
     * @param string|null $level
     *
     * @return mixed
     */
    public function handle(Request $request, Closure $next, ?string $level = null)
    {
        if (! isAdmin($level)) {
            abort(403, __('errors.forbidden'));
        }

        Log::query()->create([
            'user_id'    => getUser('id'),
            'request'    => $request->getRequestUri(),
            'referer'    => server('HTTP_REFERER'),
            'ip'         => getIp(),
            'brow'       => getBrowser(),
            'created_at' => SITETIME,
        ]);

        return $next($request);
    }
}
