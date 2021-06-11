<?php

namespace App\Http\Middleware;

use App\Models\Ban;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Lang;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\View;

class Main
{
    /**
     * Handle an incoming request.
     *
     * @param Request $request
     * @param Closure $next
     *
     * @return mixed
     */
    public function handle(Request $request, Closure $next)
    {
        if (! $this->isBanned($request)) {
            $this->frequencyLimit();

            // Сайт закрыт для гостей
            if (setting('closedsite') === 1 &&
                ! getUser() &&
                ! $request->is('register', 'login', 'recovery', 'captcha')
            ) {
                setFlash('danger', __('main.not_authorized'));
                return redirect('login');
            }

            // Сайт закрыт для всех
            if (setting('closedsite') === 2 &&
                ! isAdmin() &&
                ! $request->is('login')
            ) {
                return response()->view('pages/closed');
            }

            $route = Route::getRoutes()->match($request);
            [$path, $name] = explode('\\', $route->getActionName());

            if ($path === 'Modules') {
                View::addNamespace($name, base_path('modules/' . $name . '/resources/views'));
                Lang::addNamespace($name, base_path('modules/' . $name . '/resources/lang'));
            }
        }

        return $next($request);
    }

    /**
     * Проверка на ip-бан
     *
     * @param Request $request
     *
     * @return bool
     */
    private function isBanned(Request $request): bool
    {
        if (! isAdmin() && isset(ipBan()[getIp()])) {
            if ($request->is('ipban', 'captcha')) {
                return true;
            }

            return redirect('ipban');
        }

        return false;
    }

    /**
     * Ограничение частоты запросов
     *
     * @return void
     */
    private function frequencyLimit(): void
    {
        if (setting('doslimit')) {
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
}
