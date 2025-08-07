<?php

namespace App\Http\Middleware;

use App\Models\Ban;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;

class CheckThrottle
{
    /**
     * Handle an incoming request.
     */
    public function handle(Request $request, Closure $next)
    {
        $ip = getIp();

        // Проверка на бан
        if ($this->isBanned($request, $ip)) {
            return redirect()->route('ipban')->setStatusCode(403);
        }

        $limit = setting('doslimit');
        if (! $limit) {
            return $next($request);
        }

        $key = 'throttle_' . $ip;
        $requests = Cache::add($key, 0, 60) ? 1 : Cache::increment($key);

        /* Автоматическая блокировка */
        if ($requests > $limit) {
            if (! Ban::query()->where('ip', $ip)->exists()) {
                Ban::query()->insertOrIgnore([
                    'ip'         => $ip,
                    'created_at' => SITETIME,
                ]);

                clearCache('ipBan');
            }

            clearCache($key);
            saveErrorLog(666);

            return redirect()->route('ipban')->setStatusCode(403);
        }

        return $next($request);
    }

    /**
     * Проверка на ip-бан
     */
    private function isBanned(Request $request, string $ip): bool
    {
        if (isAdmin() || $request->is('ipban', 'captcha')) {
            return false;
        }

        $bannedIps = Cache::rememberForever('ipBan', function () {
            return Ban::query()->pluck('id', 'ip')->toArray();
        });

        return isset($bannedIps[$ip]);
    }
}
