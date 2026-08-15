<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\Online;
use App\Support\Registry;
use Illuminate\Support\Facades\Cache;
use PDOException;

class MetrikaService
{
    /**
     * Сохраняет статистику
     */
    public function saveStatistic(): void
    {
        session()->increment('hits');

        if (session('online') > now()->timestamp) {
            return;
        }

        // Чистка устаревших онлайн раз в 30с на весь сайт, а не на каждую сессию
        if (Cache::add('online_cleanup', true, 30)) {
            Online::query()->where('updated_at', '<', now()->subSeconds((int) setting('timeonline')))->delete();
        }

        $user = getUser();
        $ip = getIp();
        $brow = getBrowser();
        $uid = md5($ip . $brow);

        if ($user) {
            $user->update(['updated_at' => now()]);
        }

        try {
            $online = Online::query()
                ->where('uid', $uid)
                ->updateOrCreate([], [
                    'uid'        => $uid,
                    'ip'         => $ip,
                    'brow'       => $brow,
                    'updated_at' => now(),
                    'user_id'    => $user->id ?? null,
                ]);
            $newHost = $online->wasRecentlyCreated;
        } catch (PDOException) {
            $newHost = false;
        }

        $hits = (int) session('hits', 1);

        foreach (Registry::$onSaveStatistic as $handler) {
            $handler($newHost, $hits);
        }

        session(['hits' => 0]);
        session(['online' => now()->addSeconds(30)->timestamp]);
    }
}
