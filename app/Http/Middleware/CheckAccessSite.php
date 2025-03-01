<?php

namespace App\Http\Middleware;

use App\Models\Ban;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Lang;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\View;

class CheckAccessSite
{
    /**
     * Handle an incoming request.
     */
    public function handle(Request $request, Closure $next)
    {
        if ($this->isBanned($request)) {
            return redirect('ipban');
        }

        $this->frequencyLimit();

        // Сайт закрыт для гостей
        if (
            setting('closedsite') === 1
            && ! getUser()
            && ! $request->is('login', 'register', 'recovery', 'captcha')
        ) {
            setFlash('danger', __('main.not_authorized'));

            return redirect('login');
        }

        // Сайт закрыт для всех
        if (
            setting('closedsite') === 2
            && ! isAdmin()
            && ! $request->is('login', 'captcha', 'closed')
        ) {
            return redirect('closed');
        }

        return $next($request);
    }

    /**
     * Проверка на ip-бан
     */
    private function isBanned(Request $request): bool
    {
        $ipBan = ipBan();

        return isset($ipBan[getIp()]) && ! isAdmin() && ! $request->is('ipban', 'captcha');
    }

    /**
     * Ограничение частоты запросов
     */
    private function frequencyLimit(): void
    {
        if (empty(setting('doslimit'))) {
            return;
        }

        $key = 'request_' . getIp();
        Cache::remember($key, 60, static function () {
            return 0;
        });

        $requests = Cache::increment($key);

        /* Автоматическая блокировка */
        if ($requests > setting('doslimit')) {
            $ipban = Ban::query()->where('ip', getIp())->first();

            if (! $ipban) {
                Ban::query()->insertOrIgnore([
                    'ip'         => getIp(),
                    'created_at' => SITETIME,
                ]);
            }

            ipBan(true);
            saveErrorLog(666);
            clearCache($key);
        }
    }
}
