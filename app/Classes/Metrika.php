<?php

declare(strict_types=1);

namespace App\Classes;

use App\Models\Counter;
use App\Models\Counter24;
use App\Models\Counter31;
use App\Models\Online;
use Illuminate\Database\Capsule\Manager as DB;
use PDOException;

class Metrika
{
    /**
     * Генерирует счетчик
     *
     * @param int $online
     *
     * @return void
     */
    public function getCounter(int $online): void
    {
        $counter = $this->getResultCounter();

        if (! $counter) {
            $counter = (object) ['dayhosts' => 0, 'dayhits' => 0];
        }

        // ----------------------------------------------------------------------//
        $img   = imagecreatefrompng(HOME . '/assets/img/images/counter.png');
        $color = imagecolorallocate($img, 62, 62, 62);

        $pos = 66;
        if ($online >= 10 && $online < 100) {
            $pos = 54;
        }
        if ($online >= 100 && $online < 1000) {
            $pos = 42;
        }

        imagettftext($img, 6, 0, 14, 7, $color, HOME . '/assets/fonts/font.ttf', (string) formatShortNum($counter->dayhosts));
        imagettftext($img, 6, 0, 14, 13, $color, HOME . '/assets/fonts/font.ttf', (string) formatShortNum($counter->dayhits));
        imagettftext($img, 12, 0, $pos, 13, $color, HOME . '/assets/fonts/font.ttf', (string) $online);

        imagepng($img, UPLOADS . '/counters/counter_new.png');
        imagedestroy($img);

        rename(UPLOADS . '/counters/counter_new.png', UPLOADS . '/counters/counter.png');
    }

    /**
     * Сохраняет статистику
     *
     * @return void
     */
    public function saveStatistic(): void
    {
        $_SESSION['hits']++;

        if (isset($_SESSION['online']) && $_SESSION['online'] > SITETIME) {
            return;
        }

        $period = date('Y-m-d H:00:00', SITETIME);
        $day    = date('Y-m-d 00:00:00', SITETIME);

        Online::query()->where('updated_at', '<', SITETIME - setting('timeonline'))->delete();

        $user   = getUser();
        $ip     = getIp();
        $brow   = getBrowser();
        $uid    = md5($ip . $brow);

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
        } catch (PDOException $e) {
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
                'allhosts' => DB::connection()->raw('allhosts + 1'),
                'dayhosts' => DB::connection()->raw('dayhosts + 1'),
                'hosts24'  => DB::connection()->raw('hosts24 + 1'),
            ];
        }

        $hits = $_SESSION['hits'];

        $hitsUpdate = [
            'allhits' => DB::connection()->raw('allhits + ' . $hits),
            'dayhits' => DB::connection()->raw('dayhits + ' . $hits),
            'hits24'  => DB::connection()->raw('hits24 + ' . $hits),
        ];

        $counter->update(array_merge($hostsUpdate, $hitsUpdate));

        $_SESSION['hits']   = 0;
        $_SESSION['online'] = strtotime('+30 seconds', SITETIME);
    }

    /**
     * Returns counter result
     *
     * @return Counter|object|null
     */
    private function getResultCounter()
    {
        return Counter::query()->first();
    }
}
