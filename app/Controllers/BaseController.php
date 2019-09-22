<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Models\Ban;
use Illuminate\Database\Capsule\Manager as DB;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Lang;
use Illuminate\Support\Facades\View;

Class BaseController
{
    public function __construct()
    {
        $request = Request::createFromGlobals();

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
        if (setting('doslimit') && is_writable(STORAGE . '/antidos')) {

            $filename = STORAGE . '/antidos/' . getIp() . '.dat';

            $dosfiles = glob(STORAGE . '/antidos/*.dat');
            foreach ($dosfiles as $filename) {
                $array_filemtime = @filemtime($filename);
                if ($array_filemtime < (time() - 60)) {
                    @unlink($filename);
                }
            }
            /* Проверка на время */
            if (file_exists($filename)) {
                $file_dos = file($filename);
                $file_str = explode('|', $file_dos[0]);
                if ($file_str[0] < (time() - 60)) {
                    @unlink($filename);
                }
            }
            /* Запись логов */
            $write = time().'|'.server('REQUEST_URI').'|'.server('HTTP_REFERER').'|'.getBrowser().'|'.getUser('id').'|';
            file_put_contents($filename, $write . PHP_EOL, FILE_APPEND);

            /* Автоматическая блокировка */
            if (counterString($filename) > setting('doslimit')) {
                    $ipban = Ban::query()->where('ip', getIp())->first();

                    if (! $ipban) {
                        DB::connection()->insert(
                            'insert ignore into ban (`ip`, `created_at`) values (?, ?);',
                            [getIp(), SITETIME]
                        );
                    }

                ipBan(true);
                saveErrorLog(666);
                @unlink($filename);
            }
        }
    }
}
