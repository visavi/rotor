<?php

namespace App\Http\Middleware;

use App\Models\User;
use Closure;
use Illuminate\Http\Request;

class CheckToken
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
        $token = $request->input('token');

        if (! $token) {
            return response()->json(['error' => 'Api token missing!'], 401);
        }

        if (! $user = getUserByToken($token)) {
            return response()->json(['error' => 'Unauthorized.'], 401);
        }

        if ($user->level === User::BANNED) {
            return response()->json(['error' => 'User banned'], 401);
        }

        $request->attributes->set('user', $user);

        return $next($request);
    }
}
