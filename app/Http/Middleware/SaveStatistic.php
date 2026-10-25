<?php

namespace App\Http\Middleware;

use App\Services\MetrikaService;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class SaveStatistic
{
    public function __construct(private readonly MetrikaService $metrika)
    {
    }

    /**
     * Пишет визит, пропуская фоновые запросы клиента
     */
    public function handle(Request $request, Closure $next): Response
    {
        if ($request->isMethod('GET') && ! MetrikaService::isBackground($request)) {
            $this->metrika->saveStatistic();
        }

        return $next($request);
    }
}
