<?php

declare(strict_types=1);

namespace App\Classes;

use App\Models\Counter;
use App\Models\Online;
use Illuminate\Database\Capsule\Manager as DB;

class Metrika
{
    /**
     * Генерирует счетчик
     *
     * @return void
     */
    public function getCounter(): void
    {
        if (file_exists(STORAGE . '/temp/counter.dat')) {
            $count = json_decode(file_get_contents(STORAGE . '/temp/counter.dat'));
        } else {
            $count = (object) ['dayhosts' => 0, 'dayhits' => 0];
        }

        if (file_exists(STORAGE . '/temp/online.dat')) {
            $online = last(json_decode(file_get_contents(STORAGE . '/temp/online.dat')));
        } else {
            $online = 0;
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

        imagettftext($img, 6, 0, 14, 7, $color, HOME . '/assets/fonts/font.ttf', formatShortNum($count->dayhosts));
        imagettftext($img, 6, 0, 14, 13, $color, HOME . '/assets/fonts/font.ttf', formatShortNum($count->dayhits));
        imagettftext($img, 12, 0, $pos, 13, $color, HOME . '/assets/fonts/font.ttf', $online);

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
        $period = date('Y-m-d H:00:00', SITETIME);
        $day    = date('Y-m-d 00:00:00', SITETIME);

        Online::query()->where('updated_at', '<', SITETIME - setting('timeonline'))->delete();

        $newHost = false;

        if (getUser()) {
            $online = Online::query()
                ->where('ip', getIp())
                ->orWhere('user_id', getUser('id'))
                ->orderByRaw('user_id = ? desc', [getUser('id')])
                ->first();

            if ($online) {
                $online->update([
                    'ip'         => getIp(),
                    'brow'       => getBrowser(),
                    'updated_at' => SITETIME,
                    'user_id'    => getUser('id'),
                ]);
            } else {
                Online::query()->create([
                    'ip'         => getIp(),
                    'brow'       => getBrowser(),
                    'updated_at' => SITETIME,
                    'user_id'    => getUser('id'),
                ]);
                $newHost = true;
            }
        } else {
            $online = Online::query()
                ->where('ip', getIp())
                ->orderByRaw('user_id IS NULL desc')
                ->first();

            if ($online) {
                $online->update([
                    'brow'       => getBrowser(),
                    'updated_at' => SITETIME,
                    'user_id'    => null,
                ]);
            } else {
                Online::query()->create([
                    'ip'         => getIp(),
                    'brow'       => getBrowser(),
                    'updated_at' => SITETIME,
                ]);
                $newHost = true;
            }
        }
        // -----------------------------------------------------------//
        $counter = Counter::query()->first();

        if (date('Y-m-d 00:00:00', strtotime($counter->period)) !== $day) {
            DB::connection()->insert('insert ignore into counters31 (period, hosts, hits) values (?, ?, ?);', [$day, $counter->dayhosts, $counter->dayhits]);
            DB::connection()->update('update counters set period=?, dayhosts=?, dayhits=?;', [$period, 0, 0]);
        }

        if ($counter->period !== $period) {
            DB::connection()->insert('insert ignore into counters24 (period, hosts, hits) values (?, ?, ?);', [$period, $counter->hosts24, $counter->hits24]);
            DB::connection()->update('update counters set period=?, hosts24=?, hits24=?;', [$period, 0, 0]);
        }

        // -----------------------------------------------------------//
        if ($newHost) {
            $counter->update([
                'allhosts' => DB::connection()->raw('allhosts + 1'),
                'allhits'  => DB::connection()->raw('allhits + 1'),
                'dayhosts' => DB::connection()->raw('dayhosts + 1'),
                'dayhits'  => DB::connection()->raw('dayhits + 1'),
                'hosts24'  => DB::connection()->raw('hosts24 + 1'),
                'hits24'   => DB::connection()->raw('hits24 + 1'),
            ]);
        } else {
            $counter->update([
                'allhits' => DB::connection()->raw('allhits + 1'),
                'dayhits' => DB::connection()->raw('dayhits + 1'),
                'hits24'  => DB::connection()->raw('hits24 + 1'),
            ]);
        }
    }
}
