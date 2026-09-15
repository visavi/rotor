<?php

namespace App\Http\Middleware;

use App\Services\MetrikaService;
use Closure;
use Illuminate\Http\Request;

class SaveStatistic
{
    /**
     * Handle an incoming request.
     */
    public function handle(Request $request, Closure $next)
    {
        // Фоновый опрос клиента не означает, что человек за экраном: помимо
        // ajax отсеиваем пути, которые модули объявили фоновыми явно
        $background = $request->ajax()
            || $request->expectsJson()
            || $request->is(...MetrikaService::backgroundPaths());

        if ($request->isMethod('GET') && ! $background) {
            (new MetrikaService())->saveStatistic();
        }

        return $next($request);
    }
}
