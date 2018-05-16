<?php

namespace App\Classes;

use App\Models\Counter;
use App\Models\Counter24;
use App\Models\Counter31;
use App\Models\Online;
use Illuminate\Database\Capsule\Manager as DB;

class Metrika
{
    /**
     * Генерирует счетчик
     *
     * @return void
     */
    public function getCounter()
    {
        // -------------------- Вывод статистики ------------------------------//
        $week_day = date('w');
        $arr_week = ['вс', 'пн', 'вт', 'ср', 'чт', 'пт', 'сб'];

        if (file_exists(STORAGE . '/temp/counter.dat')) {
            $count = json_decode(file_get_contents(STORAGE . '/temp/counter.dat'));
        } else {
            $count = (object) ['dayhosts' => 0, 'dayhits' => 0];
        }

        if (file_exists(STORAGE . '/temp/online.dat')) {
            $online = current(json_decode(file_get_contents(STORAGE . '/temp/online.dat')));
        } else {
            $online = 0;
        }

        if (file_exists(STORAGE . '/temp/counter7.dat')) {
            $host_data = json_decode(file_get_contents(STORAGE . '/temp/counter7.dat'));
        } else {
            $host_data = array_fill(0, 6, 0);
        }

        $host_data[] = $count->dayhosts;
        // ----------------------------------------------------------------------//
        $img    = imagecreatefrompng(HOME . '/assets/img/images/counter.png');
        $color  = imagecolorallocate($img, 0, 0, 0);
        $color2 = imagecolorallocate($img, 102, 102, 102);

        $pos = 65;
        if ($online >= 10 && $online < 100) $pos = 52;
        if ($online >= 100 && $online < 200) $pos = 44;
        if ($online >= 200 && $online < 1000) $pos = 40;

        $colors = [
            [191, 109, 232],
            [87,  164, 246],
            [0,   203, 189],
            [0,   199, 68],
            [149, 217, 0],
            [255, 255, 0],
            [255, 181, 0]
        ];

        $max = max($host_data);

        if ($max == 0) {
            $max = 1;
        }

        $per_host = [];
        foreach ($host_data as $value) {
            $per_host[] = $value * 0.90 / $max;
        }

        $coll = 11;
        $im   = 14;
        $x1   = 2;
        $y2   = 15;
        $x2   = $x1 + $coll;

        foreach ($per_host as $key => $percent) {
            $week_day++;

            if ($week_day > 6) {
                $week_day -= 7;
            }

            $y1 = round($im - $im * $percent + 1);

            if (!empty($percent)) {
                $col = imagecolorallocate($img, $colors[$key][0], $colors[$key][1], $colors[$key][2]);
                imagefilledrectangle($img, $x1-1, $y1, $x2-2, $y2, $col);
            }

            imagettftext($img, 6, 0, $x1, 15, $color, HOME . '/assets/fonts/font.ttf', $arr_week[$week_day]);

            $x1 += $coll;
            $x2 += $coll;
        }

        imagettftext($img, 6, 0, 13, 23, $color2, HOME . '/assets/fonts/font4.ttf', formatShortNum($count->dayhosts));
        imagettftext($img, 6, 0, 13, 29, $color2, HOME . '/assets/fonts/font4.ttf', formatShortNum($count->dayhits));
        imagettftext($img, 12, 0, $pos, 29, $color2, HOME . '/assets/fonts/font7.ttf', $online);

        imagepng($img, UPLOADS . '/counters/counter_new.png');
        imagedestroy($img);

        rename(UPLOADS . '/counters/counter_new.png', UPLOADS . '/counters/counter.png');
    }

    /**
     * Выводит счетчик за день
     *
     * @return string
     */
    public function getCounterDay()
    {
        $imageCache = '/uploads/counters/counter24.gif';
        if (!file_exists($imageCache) || dateFixed(@filemtime($imageCache), "dmYH") != dateFixed(SITETIME, "dmYH")){

            $hours = floor((gmmktime(date("H"), 0, 0, date("m"), date("d"), date("Y")) - gmmktime((date("Z") / 3600), 0, 0, 1, 1, 1970)) / 3600);

            $counts = Counter24::query()->orderBy('hour', 'desc')->get();

            $arrhits   = [];
            $arrhosts  = [];
            $hits_data = [];
            $host_data = [];

            foreach ($counts as $val) {
                $arrhits[$val['hour']]  = $val['hits'];
                $arrhosts[$val['hour']] = $val['hosts'];
            }

            for ($i = 0, $tekhours = $hours; $i < 24; $tekhours -= 1, $i++) {
                $hits_data[] = $arrhits[$tekhours] ?? 0;
                $host_data[] = $arrhosts[$tekhours] ?? 0;
            }
            // --------------------------------------------------//
            $maxhit = 0;
            $max_index = 0;
            foreach ($hits_data as $index => $value) {
                if ($value > $maxhit) {
                    $maxhit = $value;
                    $max_index = $index;
                }
            }
            $maxhost = max($host_data);
            if ($maxhit == 0) {
                $maxhit = 1;
            }
            // процентное соотношение хитов
            $per_hit = [];
            foreach ($hits_data as $value) {
                $per_hit[] = $value * 0.90 / $maxhit;
            }
            // процентное соотношение хостов
            $per_host = [];
            foreach ($host_data as $value) {
                $per_host[] = $value * 2.90 / $maxhit;
            }
            $img = @imagecreatefromgif(HOME . '/assets/img/images/counter24.gif');
            // линейный
            $color1 = imagecolorallocate($img, 44, 191, 228);
            $color2 = imagecolorallocate($img, 0, 0, 120);
            $color_red = imagecolorallocate($img, 200, 0, 0);

            $image = 47;
            $coll = 3;
            $x1 = 119;
            $x2 = $x1 - 3;
            $y1_hits = (int)($image - $image * $per_hit[0] + 7);
            $y1_host = (int)($image - $image * $per_host[0] + 7);

            $counth = count($hits_data);
            if ($counth > 24) {
                $counth = 24;
            }

            for($i = 1;$i < $counth;$i++) {
                // хиты
                $y2_hits = (int)($image - $image * $per_hit[$i] + 7);
                imageline($img, $x1 + 1, $y1_hits, $x2, $y2_hits, $color1);
                // хосты
                $y2_host = (int)($image - $image * $per_host[$i] + 7);
                imageline($img, $x1 + 1, $y1_host, $x2, $y2_host, $color2);

                if ($hits_data[$i] != 0 && $i == $max_index) {
                    imagettftext($img, 6, 0, 40, $y2_hits-3, $color_red, HOME . '/assets/fonts/font.ttf', 'max. ' . $maxhost.' / ' . $maxhit);
                    imageline($img, $x2-1, $y2_hits-2, $x2-1, $y2_hits + 42, $color_red);
                }
                $y1_hits = $y2_hits;
                $y1_host = $y2_host;
                $x1 -= $coll + 2;
                $x2 -= $coll + 2;
            }
            //Header("Content-type: image/gif");
            imagegif($img, HOME . $imageCache);
            imagedestroy($img);
        }

        echo '<img src="' . $imageCache . '?' . dateFixed(SITETIME, "dmYH") . '" alt="Сутки"><br><br>';
    }

    /**
     * Выводит счетчик за неделю
     *
     * @return string
     */
    public function getCounterWeek()
    {
        $imageCache = '/uploads/counters/counter7.gif';
        if (!file_exists($imageCache) || dateFixed(@filemtime($imageCache), "dmY") != dateFixed(SITETIME, "dmY")){

            $week_day = date("w") - 1;
            $arr_week = ['вс', 'пн', 'вт', 'ср', 'чт', 'пт', 'сб'];
            $days = floor((gmmktime(0, 0, 0, date("m"), date("d"), date("Y")) - gmmktime(0, 0, 0, 1, 1, 1970)) / 86400);

            $counts = Counter31::query()
                ->orderBy('days', 'desc')
                ->limit(7)
                ->pluck('hosts', 'days')
                ->all();

            $host_data = [];
            for ($i = 0, $tekdays = $days; $i < 7; $tekdays--, $i++) {
                $host_data[] = $counts[$tekdays] ?? 0;
            }

            $host_data = array_reverse($host_data);
            // ---------------------------------------------//
            $max = max($host_data);
            if ($max == 0) $max = 1;

            $per_host = [];
            foreach ($host_data as $value) {
                $per_host[] = $value * 0.90 / $max;
            }

            $img = imagecreatefromgif(HOME . '/assets/img/images/counter7.gif');

            $imageW = 47;
            $collW  = 14;

            $x1 = 12;
            $y2 = 59;
            $x2 = $x1 + $collW;
            $colorBlack = imagecolorallocate($img, 0, 0, 120);

            foreach ($per_host as $index => $percent) {
                $week_day++;
                if ($week_day > 6) {
                    $week_day -= 7;
                }

                if ($index / 2 == (int)($index / 2)) {
                    $color = imagecolorallocate($img, 249, 243, 70);
                    $color2 = imagecolorallocate($img, 242, 226, 42);
                    $color3 = imagecolorallocate($img, 226, 210, 34);
                } else {
                    $color = imagecolorallocate($img, 11, 215, 252);
                    $color2 = imagecolorallocate($img, 7, 203, 239);
                    $color3 = imagecolorallocate($img, 7, 187, 219);
                }

                $y1 = round($imageW - $imageW * $percent + 12);
                imagefilledrectangle($img, $x1, $y1, $x2, $y2, $color);

                $points = [0 => $x1, // x1
                           1 => $y1, // y1
                           2 => $x1 + 3, // x2
                           3 => $y1-5, // y2
                           4 => $x1 + $collW + 3, // x3
                           5 => $y1-5, // y3
                           6 => $x2, // x4
                           7 => $y1, // y4
                ];

                imagefilledpolygon($img, $points, 4, $color2);

                $points = [0 => $x2, // x1
                           1 => $y1, // y1
                           2 => $x1 + $collW + 3, // x2
                           3 => $y1-5, // y2
                           4 => $x1 + $collW + 3, // x3
                           5 => $y2-5, // y3
                           6 => $x2, // x4
                           7 => $y2, // y4
                ];

                imagefilledpolygon($img, $points, 4, $color3);
                imagettftext($img, 6, 90, $x1+10, 50, $colorBlack, HOME . '/assets/fonts/font.ttf', $host_data[$index]);
                imagettftext($img, 6, 0, $x1 + 3, 66, $colorBlack, HOME . '/assets/fonts/font.ttf', $arr_week[$week_day]);

                $x1 += $collW;
                $x2 += $collW;
            }
            //Header("Content-type: image/gif");
            imagegif($img, HOME.$imageCache);
            imagedestroy($img);
        }

        echo '<img src="' . $imageCache . '?'.dateFixed(SITETIME, "dmY") . '" alt="Неделя"><br><br>';
    }

    /**
     * Выводит счетчик за месяц
     *
     * @return string
     */
    public function getCounterMonth()
    {
        $imageCache = '/uploads/counters/counter31.gif';
        if (!file_exists($imageCache) || dateFixed(@filemtime($imageCache), "dmY") != dateFixed(SITETIME, "dmY")){

            $days = floor((gmmktime(0, 0, 0, date("m"), date("d"), date("Y")) - gmmktime(0, 0, 0, 1, 1, 1970)) / 86400);

            $counts = Counter31::query()->orderBy('days', 'desc')->get();

            $arrhits   = [];
            $arrhosts  = [];
            $hits_data = [];
            $host_data = [];

            foreach ($counts as $val) {
                $arrhits[$val['days']]  = $val['hits'];
                $arrhosts[$val['days']] = $val['hosts'];
            }

            for ($i = 0, $tekdays = $days; $i < 31; $tekdays -= 1, $i++) {
                $hits_data[] = $arrhits[$tekdays] ?? 0;
                $host_data[] = $arrhosts[$tekdays] ?? 0;
            }
            // --------------------------------------------------//
            $maxhit = 0;
            $max_index = 0;
            foreach ($hits_data as $index => $value) {
                if ($value > $maxhit) {
                    $maxhit = $value;
                    $max_index = $index;
                }
            }

            $maxhost = max($host_data);
            if ($maxhit == 0) {
                $maxhit = 1;
            }
            // процентное соотношение хитов
            $per_hit = [];
            foreach ($hits_data as $value) {
                $per_hit[] = $value * 0.90 / $maxhit;
            }
            // процентное соотношение хостов
            $per_host = [];
            foreach ($host_data as $value) {
                $per_host[] = $value * 2.90 / $maxhit;
            }
            $img = imagecreatefromgif(HOME . '/assets/img/images/counter31.gif');
            // линейный
            $color1 = imagecolorallocate($img, 44, 191, 228);
            $color2 = imagecolorallocate($img, 0, 0, 120);
            $color_red = imagecolorallocate($img, 200, 0, 0);

            $image = 47;
            $coll = 3;
            $x1 = 154;
            $x2 = $x1 - 3;
            $y1_hits = (int)($image - $image * $per_hit[0] + 7);
            $y1_host = (int)($image - $image * $per_host[0] + 7);

            $counth = count($hits_data);
            if ($counth > 31) {
                $counth = 31;
            }

            imagettftext($img, 6, 0, 50, 7, $color_red, HOME . '/assets/fonts/font.ttf', 'max. ' . $maxhost . ' / ' . $maxhit);

            for($i = 1;$i < $counth;$i++) {
                // хиты
                $y2_hits = (int)($image - $image * $per_hit[$i] + 7);
                imageline($img, $x1 + 1, $y1_hits, $x2, $y2_hits, $color1);
                // хосты
                $y2_host = (int)($image - $image * $per_host[$i] + 7);
                imageline($img, $x1 + 1, $y1_host, $x2, $y2_host, $color2);

                if ($hits_data[$i] != 0 && $i == $max_index) {
                    imageline($img, $x2-1, $y2_hits-2, $x2-1, $y2_hits + 42, $color_red);
                }
                $y1_hits = $y2_hits;
                $y1_host = $y2_host;
                $x1 -= $coll + 2;
                $x2 -= $coll + 2;
            }
            //Header("Content-type: image/gif");
            imagegif($img, HOME . $imageCache);
            imagedestroy($img);
        }

        echo '<img src="' . $imageCache . '?' . dateFixed(SITETIME, "dmY") . '" alt="Месяц"><br><br>';
    }

    /**
     * Сохраняет статистику
     *
     * @return void
     */
    public function saveStatistic()
    {
        $days  = floor((gmmktime(0, 0, 0, date("m"), date("d"), date("Y")) - gmmktime(0, 0, 0, 1, 1, 1970)) / 86400);
        $hours = floor((gmmktime(date("H"), 0, 0, date("m"), date("d"), date("Y")) - gmmktime((date("Z") / 3600), 0, 0, 1, 1, 1970)) / 3600);

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

        if ($counter->hours != $hours) {
            DB::insert("insert ignore into `counters24` (`hour`, `hosts`, `hits`) values (?, ?, ?);", [$hours, $counter->hosts24, $counter->hits24]);
            DB::update("update `counters` set `hours`=?, `hosts24`=?, `hits24`=?;", [$hours, 0, 0]);
            DB::delete("delete from `counters24` where `hour` < (select min(`hour`) from (select `hour` from `counters24` order by `hour` desc limit 24) as del);");
        }

        if ($counter->days != $days) {
            DB::insert("insert ignore into `counters31` (`days`, `hosts`, `hits`) values (?, ?, ?);", [$days, $counter->dayhosts, $counter->dayhits]);
            DB::update("update `counters` set `days`=?, `dayhosts`=?, `dayhits`=?;", [$days, 0, 0]);
            DB::delete("delete from `counters31` where `days` < (select min(`days`) from (select `days` from `counters31` order by `days` desc limit 31) as del);");
            // ---------------------------------------------------//

            $week = Counter31::query()
                ->orderBy('days', 'desc')
                ->limit(6)
                ->pluck('hosts', 'days')
                ->all();

            $hostData = [];
            for ($i = 0, $tekdays = $days; $i < 6; $tekdays--, $i++) {
                array_unshift($hostData, $week[$tekdays] ?? 0);
            }

            file_put_contents(STORAGE . '/temp/counter7.dat', json_encode($hostData), LOCK_EX);
        }

        // -----------------------------------------------------------//
        if ($newHost) {
            $counter->update([
                'allhosts' => DB::raw('allhosts + 1'),
                'allhits'  => DB::raw('allhits + 1'),
                'dayhosts' => DB::raw('dayhosts + 1'),
                'dayhits'  => DB::raw('dayhits + 1'),
                'hosts24'  => DB::raw('hosts24 + 1'),
                'hits24'   => DB::raw('hits24 + 1'),
            ]);
        } else {
            $counter->update([
                'allhits' => DB::raw('allhits + 1'),
                'dayhits' => DB::raw('dayhits + 1'),
                'hits24'  => DB::raw('hits24 + 1'),
            ]);
        }
    }
}
