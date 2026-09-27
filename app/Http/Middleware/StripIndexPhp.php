<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class StripIndexPhp
{
    /**
     * Адрес с именем скрипта (/index.php/forums) уводит на чистый (/forums).
     * Иначе сайт отвечает дублем, и имя скрипта попадает в canonical и все ссылки страницы:
     * url() и route() строятся от базового адреса запроса
     */
    public function handle(Request $request, Closure $next): Response
    {
        $script = $request->getScriptName();

        // 301 превратил бы POST в GET, форма потеряла бы данные
        if ($script === '' || ! $request->isMethodSafe() || $request->getBaseUrl() !== $script) {
            return $next($request);
        }

        // Остаток исходного адреса без перекодирования: путь и строка запроса как пришли
        $rest = substr($request->getRequestUri(), strlen($request->getBaseUrl()));

        // Относительный Location: url() добавил бы к нему тот же /index.php
        return new RedirectResponse($request->getBasePath() . '/' . ltrim($rest, '/'), 301);
    }
}
