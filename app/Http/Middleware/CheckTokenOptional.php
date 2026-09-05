<?php

namespace App\Http\Middleware;

use App\Models\User;
use App\Services\MetrikaService;
use Closure;
use Illuminate\Http\Request;

class CheckTokenOptional
{
    /**
     * Авторизует по токену, если он передан. Без токена запрос выполняется как гостевой
     */
    public function handle(Request $request, Closure $next)
    {
        $token = $request->bearerToken();

        if (! $token) {
            return $next($request);
        }

        if (! $user = User::query()->where('apikey', $token)->first()) {
            abort(401, 'Unauthorized');
        }

        if ($user->level === User::BANNED) {
            abort(403, 'User banned');
        }

        auth()->setUser($user);

        (new MetrikaService())->saveVisit($user);

        return $next($request);
    }
}
