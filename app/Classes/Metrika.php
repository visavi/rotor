<?php

declare(strict_types=1);

namespace App\Classes;

use App\Models\Counter;
use App\Models\Counter24;
use App\Models\Counter31;
use App\Models\Online;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use PDOException;

class Metrika
{
    /**
     * Закэшированный счетчик в пределах жизни инстанса
     */
    private ?Counter $resultCounter = null;

    /**
     * Сохраняет статистику
     */
    public function saveStatistic(): void
    {
        session()->increment('hits');

        if (session('online') > SITETIME) {
            return;
        }

        $period = date('Y-m-d H:00:00', SITETIME);
        $day = date('Y-m-d 00:00:00', SITETIME);

        // Чистка устаревших онлайн раз в 30с на весь сайт, а не на каждую сессию
        if (Cache::add('online_cleanup', true, 30)) {
            Online::query()->where('updated_at', '<', SITETIME - setting('timeonline'))->delete();
        }

        $user = getUser();
        $ip = getIp();
        $brow = getBrowser();
        $uid = md5($ip . $brow);

        if ($user) {
            $user->update(['updated_at' => SITETIME]);
        }

        try {
            $online = Online::query()
                ->where('uid', $uid)
                ->updateOrCreate([], [
                    'uid'        => $uid,
                    'ip'         => $ip,
                    'brow'       => $brow,
                    'updated_at' => SITETIME,
                    'user_id'    => $user->id ?? null,
                ]);
            $newHost = $online->wasRecentlyCreated;
        } catch (PDOException) {
            $newHost = false;
        }

        // -----------------------------------------------------------//
        $counter = $this->getResultCounter();
        if (! $counter) {
            return;
        }

        if (date('Y-m-d 00:00:00', strtotime($counter->period)) !== $day) {
            Counter31::query()->insertOrIgnore([
                'period' => $period,
                'hosts'  => $counter->dayhosts,
                'hits'   => $counter->dayhits,
            ]);

            $counter->update([
                'dayhosts' => 0,
                'dayhits'  => 0,
            ]);
        }

        if ($counter->period !== $period) {
            Counter24::query()->insertOrIgnore([
                'period' => $period,
                'hosts'  => $counter->hosts24,
                'hits'   => $counter->hits24,
            ]);

            $counter->update([
                'period'  => $period,
                'hosts24' => 0,
                'hits24'  => 0,
            ]);
        }

        // -----------------------------------------------------------//
        $hostsUpdate = [];
        if ($newHost) {
            $hostsUpdate = [
                'allhosts' => DB::raw('allhosts + 1'),
                'dayhosts' => DB::raw('dayhosts + 1'),
                'hosts24'  => DB::raw('hosts24 + 1'),
            ];
        }

        $hits = (int) session('hits', 1);

        $hitsUpdate = [
            'allhits' => DB::raw('allhits + ' . $hits),
            'dayhits' => DB::raw('dayhits + ' . $hits),
            'hits24'  => DB::raw('hits24 + ' . $hits),
        ];

        $counter->update(array_merge($hostsUpdate, $hitsUpdate));
        $counter->refresh();

        session(['hits' => 0]);
        session(['online' => strtotime('+30 seconds', SITETIME)]);
    }

    /**
     * Returns counter result
     */
    private function getResultCounter(): ?Counter
    {
        return $this->resultCounter ??= Counter::query()->first();
    }
}
