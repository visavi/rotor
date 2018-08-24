<?php

namespace App\Controllers;

use App\Classes\Request;
use App\Models\Ban;
use App\Models\Error;
use Illuminate\Database\Capsule\Manager as DB;

Class BaseController
{
    public function __construct()
    {
        /**
         * Проверка на ip-бан
         */
        if ($ipBan = ipBan()) {

            $ipSplit = explode('.', getIp());

            foreach($ipBan as $ip) {
                $matches = 0;
                $dbSplit = explode('.', $ip);

                foreach($ipSplit as $key => $split) {
                    if (isset($dbSplit[$key]) && ($split === $dbSplit[$key] || $dbSplit[$key] === '*')) {
                        ++$matches;
                    }
                }

                if ($matches === 4 && ! Request::is('banip', 'captcha')) {
                    redirect('/banip');
                }
            }
        }

        /**
         * Счетчик запросов
         */
        if (setting('doslimit') && is_writable(STORAGE . '/antidos')) {

            $dosfiles = glob(STORAGE.'/antidos/*.dat');
            foreach ($dosfiles as $filename) {
                $array_filemtime = @filemtime($filename);
                if ($array_filemtime < (time() - 60)) {
                    @unlink($filename);
                }
            }
            // -------------------------- Проверка на время -----------------------------//
            if (file_exists(STORAGE.'/antidos/'.getIp().'.dat')) {
                $file_dos = file(STORAGE.'/antidos/'.getIp().'.dat');
                $file_str = explode('|', $file_dos[0]);
                if ($file_str[0] < (time() - 60)) {
                    @unlink(STORAGE.'/antidos/'.getIp().'.dat');
                }
            }
            // ------------------------------ Запись логов -------------------------------//
            $write = time().'|'.server('REQUEST_URI').'|'.server('HTTP_REFERER').'|'.getBrowser().'|'.getUser('login').'|';
            file_put_contents(STORAGE.'/antidos/'.getIp().'.dat', $write."\r\n", FILE_APPEND);

            // ----------------------- Автоматическая блокировка ------------------------//
            if (counterString(STORAGE.'/antidos/'.getIp().'.dat') > setting('doslimit')) {

                if (!empty(setting('errorlog'))) {

                    $banip = Ban::query()->where('ip', getIp())->first();

                    if (! $banip) {
                        Error::query()->create([
                            'code'       => 666,
                            'request'    => utfSubstr(server('REQUEST_URI'), 0, 200),
                            'referer'    => utfSubstr(server('HTTP_REFERER'), 0, 200),
                            'user_id'    => getUser('id'),
                            'ip'         => getIp(),
                            'brow'       => getBrowser(),
                            'created_at' => SITETIME,

                        ]);

                        DB::insert(
                            'insert ignore into ban (`ip`, `created_at`) values (?, ?);',
                            [getIp(), SITETIME]
                        );

                        ipBan(true);
                    }
                }

                unlink(STORAGE.'/antidos/'.getIp().'.dat');
            }
        }

        // Сайт закрыт для гостей
        if (setting('closedsite') == 1 && ! getUser() && ! Request::is('register', 'login', 'recovery', 'captcha')) {
            setFlash('danger', 'Для входа на сайт необходимо авторизоваться!');
            redirect('/login');
        }

        // Сайт закрыт для всех
        if (setting('closedsite') == 2 && ! isAdmin() && ! Request::is('closed', 'login')) {
            redirect('/closed');
        }
    }
}
