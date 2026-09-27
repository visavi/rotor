<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class NormalizeUrl
{
    /**
     * Один адрес на страницу: /index.php/forums/ уводит на /forums.
     * Имя скрипта в адресе опаснее всего — url() и route() строятся от базового адреса запроса,
     * и /index.php попадает в canonical и все ссылки страницы
     */
    public function handle(Request $request, Closure $next): Response
    {
        // 301 превратил бы POST в GET, форма потеряла бы данные
        if (! $request->isMethodCacheable()) {
            return $next($request);
        }

        $baseUrl = $request->getBaseUrl();
        $script = $request->getScriptName();
        $requestUri = $request->getRequestUri();

        // baseUrl не из адреса: раскладка «всё в public_html» с /index.php в адресе даёт /public/index.php,
        // и Symfony теряет путь. Такой адрес уводит правило в public/.htaccess
        if (! str_starts_with($requestUri, $baseUrl)) {
            return $next($request);
        }

        // Остаток исходного адреса без перекодирования: путь и строка запроса как пришли
        [$path, $query] = array_pad(explode('?', substr($requestUri, strlen($baseUrl)), 2), 2, null);

        // Имя скрипта в адресе может прийти закодированным (/index%2Ephp): сервер его раскодирует,
        // а baseUrl остаётся как в запросе. Каталог отрезается вручную — getBasePath() тоже закодирован
        $scriptInUrl = $script !== '' && rawurldecode($baseUrl) === $script;
        $basePath = $scriptInUrl ? substr($baseUrl, 0, (int) strrpos($baseUrl, '/')) : $baseUrl;

        $trimmed = trim($path, '/');

        if ($trimmed !== '') {
            // Один ведущий слэш: //evil.com в Location увёл бы на чужой хост
            $path = '/' . $trimmed;
        } elseif ($scriptInUrl) {
            $path = '/';
        }
        // Корень не трогаем: корень подкаталога — каталог, слэш у него дописывает сам сервер

        $target = $basePath . $path . ($query !== null ? '?' . $query : '');

        if ($target === $requestUri) {
            return $next($request);
        }

        // Относительный Location: url() добавил бы к нему тот же /index.php
        return new RedirectResponse($target, 301);
    }
}
