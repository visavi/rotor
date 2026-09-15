<?php

namespace App\Http\Middleware;

use App\Services\MetrikaService;
use Closure;
use Illuminate\Http\Request;

class SaveStatistic
{
    /**
     * Пишет визит, пропуская фоновые запросы клиента
     */
    public function handle(Request $request, Closure $next)
    {
        if ($request->isMethod('GET') && ! MetrikaService::isBackground($request)) {
            (new MetrikaService())->saveStatistic();
        }

        return $next($request);
    }
}
