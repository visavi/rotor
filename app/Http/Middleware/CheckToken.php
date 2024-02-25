<?php

namespace App\Http\Middleware;

use App\Models\User;
use Closure;
use Illuminate\Http\Request;

class CheckToken
{
    /**
     * Handle an incoming request.
     */
    public function handle(Request $request, Closure $next)
    {
        $token = $request->input('token');

        if (! $token) {
            abort(400, 'Api token missing');
        }

        if (! $user = getUserByToken($token)) {
            abort(401, 'Unauthorized');
        }

        if ($user->level === User::BANNED) {
            abort(403, 'User banned');
        }

        $request->attributes->set('user', $user);

        return $next($request);
    }
}
