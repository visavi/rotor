<?php

namespace App\Http\Middleware;

use App\Traits\AuthenticatesToken;
use Closure;
use Illuminate\Http\Request;

class CheckToken
{
    use AuthenticatesToken;

    /**
     * Пропускает только запросы с валидным токеном
     */
    public function handle(Request $request, Closure $next)
    {
        $token = $request->bearerToken();

        if (! $token) {
            abort(400, 'Api token missing');
        }

        $this->authenticateToken($token);

        return $next($request);
    }
}
