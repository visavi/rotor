<?php

namespace App\Http\Middleware;

use App\Services\MetrikaService;
use App\Traits\AuthenticatesToken;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class CheckToken
{
    use AuthenticatesToken;

    public function __construct(
        private readonly MetrikaService $metrikaService,
    ) {
        //
    }

    /**
     * Пропускает только запросы с валидным токеном
     */
    public function handle(Request $request, Closure $next): Response
    {
        $token = $request->bearerToken();

        if (! $token) {
            abort(400, 'Api token missing');
        }

        $this->authenticateToken($token, $request, $this->metrikaService);

        return $next($request);
    }
}
