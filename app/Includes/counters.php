<?php

use App\Models\Counter;
use App\Models\Counter31;
use App\Models\Online;
use Illuminate\Database\Capsule\Manager as DB;

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
    DB::insert("insert ignore into `counter24` (`hour`, `hosts`, `hits`) values (?, ?, ?);", [$hours, $counter->hosts24, $counter->hits24]);
    DB::update("update `counter` set `hours`=?, `hosts24`=?, `hits24`=?;", [$hours, 0, 0]);
    DB::delete("delete from `counter24` where `hour` < (select min(`hour`) from (select `hour` from `counter24` order by `hour` desc limit 24) as del);");
}

if ($counter->days != $days) {
    DB::insert("insert ignore into `counter31` (`days`, `hosts`, `hits`) values (?, ?, ?);", [$days, $counter->dayhosts, $counter->dayhits]);
    DB::update("update `counter` set `days`=?, `dayhosts`=?, `dayhits`=?;", [$days, 0, 0]);
    DB::delete("delete from `counter31` where `days` < (select min(`days`) from (select `days` from `counter31` order by `days` desc limit 31) as del);");
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

    file_put_contents(STORAGE.'/temp/counter7.dat', json_encode($hostData), LOCK_EX);
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

