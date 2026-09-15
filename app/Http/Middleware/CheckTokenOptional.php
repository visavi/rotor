<?php

namespace App\Http\Middleware;

use App\Traits\AuthenticatesToken;
use Closure;
use Illuminate\Http\Request;

class CheckTokenOptional
{
    use AuthenticatesToken;

    /**
     * Авторизует по токену, если он передан. Без токена запрос выполняется как гостевой
     */
    public function handle(Request $request, Closure $next)
    {
        $token = $request->bearerToken();

        if ($token) {
            $this->authenticateToken($token);
        }

        return $next($request);
    }
}
