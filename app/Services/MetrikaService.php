<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\Online;
use App\Models\User;
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

        $this->cleanupOnline();

        $user = getUser();

        $newHost = $this->touchVisit($user);
        $hits = (int) session('hits', 1);

        foreach (Registry::$onSaveStatistic as $handler) {
            $handler($newHost, $hits);
        }

        session(['hits' => 0]);
        session(['online' => now()->addSeconds(30)->timestamp]);
    }

    /**
     * Сохраняет визит пользователя, авторизованного по api-токену
     */
    public function saveVisit(User $user): void
    {
        // Троттлинг, чтобы не писать в базу на каждый запрос api
        if (! Cache::add('visit_' . $user->id, true, 30)) {
            return;
        }

        $this->cleanupOnline();
        $this->touchVisit($user);
    }

    /**
     * Обновляет время визита пользователя и запись в онлайне
     *
     * @return bool Признак нового хоста
     */
    private function touchVisit(?User $user): bool
    {
        $ip = getIp();
        $brow = getBrowser();
        $uid = md5($ip . $brow);

        $user?->update(['updated_at' => now()]);

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

            return $online->wasRecentlyCreated;
        } catch (PDOException) {
            return false;
        }
    }

    /**
     * Чистит устаревшие записи онлайна раз в 30с на весь сайт, а не на каждый визит
     */
    private function cleanupOnline(): void
    {
        if (Cache::add('online_cleanup', true, 30)) {
            Online::query()->where('updated_at', '<', now()->subSeconds((int) setting('timeonline')))->delete();
        }
    }
}
