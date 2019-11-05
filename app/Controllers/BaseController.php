<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Models\Ban;
use Illuminate\Database\Capsule\Manager as DB;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Lang;
use Illuminate\Support\Facades\View;

Class BaseController
{
    public function __construct()
    {
        $request = request();

        if (! $this->isBanned($request)) {
            $this->frequencyLimit();

            // Сайт закрыт для гостей
            if (setting('closedsite') === 1 && ! getUser() && ! $request->is('register', 'login', 'recovery', 'captcha')) {
                setFlash('danger', __('main.not_authorized'));
                redirect('/login');
            }

            // Сайт закрыт для всех
            if (setting('closedsite') === 2 && ! isAdmin() && ! $request->is('closed', 'login')) {
                redirect('/closed');
            }

            [$path, $name] = explode('\\', static::class);

            if ($path === 'Modules') {
                View::addNamespace($name, MODULES . '/' . $name . '/resources/views');
                Lang::addNamespace($name, MODULES . '/' . $name . '/resources/lang');
            }
        }
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
        if (($ipBan = ipBan()) && ! isAdmin()) {

            $ipSplit = explode('.', getIp());

            foreach($ipBan as $ip) {
                $matches = 0;
                $dbSplit = explode('.', $ip);

                foreach($ipSplit as $key => $split) {
                    if (isset($dbSplit[$key]) && ($split === $dbSplit[$key] || $dbSplit[$key] === '*')) {
                        ++$matches;
                    }
                }

                if ($matches === 4) {
                    if ($request->is('ipban', 'captcha')) {
                        return true;
                    }

                    redirect('/ipban');
                }
            }
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
                    DB::connection()->insert(
                        'insert ignore into ban (`ip`, `created_at`) values (?, ?);',
                        [getIp(), SITETIME]
                    );
                }

                ipBan(true);
                saveErrorLog(666);
                clearCache($key);
            }
        }
    }
}
