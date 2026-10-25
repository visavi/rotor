<?php

namespace App\Http\Middleware;

use App\Services\MetrikaService;
use App\Traits\AuthenticatesToken;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class CheckTokenOptional
{
    use AuthenticatesToken;

    public function __construct(
        private readonly MetrikaService $metrikaService,
    ) {
        //
    }

    /**
     * Авторизует по токену, если он передан. Без токена запрос выполняется как гостевой
     */
    public function handle(Request $request, Closure $next): Response
    {
        $token = $request->bearerToken();

        if ($token) {
            $this->authenticateToken($token, $this->metrikaService);
        }

        return $next($request);
    }
}
