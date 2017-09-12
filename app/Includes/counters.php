<?php

use App\Models\Counter;
use App\Models\Counter31;
use App\Models\Online;
use Illuminate\Database\Capsule\Manager as DB;

$days  = floor((gmmktime(0, 0, 0, date("m"), date("d"), date("Y")) - gmmktime(0, 0, 0, 1, 1, 1970)) / 86400);
$hours = floor((gmmktime(date("H"), 0, 0, date("m"), date("d"), date("Y")) - gmmktime((date("Z") / 3600), 0, 0, 1, 1, 1970)) / 3600);

Online::where('updated_at', '<', SITETIME - setting('timeonline'))->delete();

$newHost = false;

if (isUser()) {
    $online = Online::where('ip', getClientIp())
        ->orWhere('user_id', getUserId())
        ->orderByRaw('user_id = ? desc', [getUserId()])
        ->first();

    if ($online) {
        $online->update([
            'ip'         => getClientIp(),
            'brow'       => getUserAgent(),
            'updated_at' => SITETIME,
            'user_id'    => getUserId(),
        ]);
    } else {
        Online::create([
            'ip'         => getClientIp(),
            'brow'       => getUserAgent(),
            'updated_at' => SITETIME,
            'user_id'    => getUserId(),
        ]);
        $newHost = true;
    }
} else {
    $online = Online::where('ip', getClientIp())
        ->orderByRaw('user_id IS NULL desc')
        ->first();

    if ($online) {
        $online->update([
            'brow'       => getUserAgent(),
            'updated_at' => SITETIME,
            'user_id'    => null,
        ]);
    } else {
        Online::create([
            'ip'         => getClientIp(),
            'brow'       => getUserAgent(),
            'updated_at' => SITETIME,
        ]);
        $newHost = true;
    }
}
// -----------------------------------------------------------//
$counter = Counter::first();

if ($counter->hours != $hours) {
    DB::insert("INSERT IGNORE INTO `counter24` (`hour`, `hosts`, `hits`) VALUES (?, ?, ?);", [$hours, $counter->hosts24, $counter->hits24]);
    DB::update("UPDATE `counter` SET `hours`=?, `hosts24`=?, `hits24`=?;", [$hours, 0, 0]);
    DB::delete("DELETE FROM `counter24` WHERE `hour` < (SELECT MIN(`hour`) FROM (SELECT `hour` FROM `counter24` ORDER BY `hour` DESC LIMIT 24) AS del);");
}

if ($counter->days != $days) {
    DB::insert("INSERT IGNORE INTO `counter31` (`days`, `hosts`, `hits`) VALUES (?, ?, ?);", [$days, $counter->dayhosts, $counter->dayhits]);
    DB::update("UPDATE `counter` SET `days`=?, `dayhosts`=?, `dayhits`=?;", [$days, 0, 0]);
    DB::delete("DELETE FROM `counter31` WHERE `days` < (SELECT MIN(`days`) FROM (SELECT `days` FROM `counter31` ORDER BY `days` DESC LIMIT 31) AS del);");
    // ---------------------------------------------------//

    $week = Counter31::orderBy('days', 'desc')
        ->limit(6)
        ->pluck('hosts', 'days')
        ->all();

    $hostData = [];
    for ($i = 0, $tekdays = $days; $i < 6; $tekdays--, $i++) {
        array_unshift($hostData, $week[$tekdays] ?? 0);
    }

    file_put_contents(STORAGE.'/temp/counter7.dat', serialize($hostData), LOCK_EX);
}

// -----------------------------------------------------------//
if ($newHost) {
    $counter->update([
        'allhits' => DB::raw('allhits + 1'),
        'dayhits' => DB::raw('dayhits + 1'),
        'hits24'  => DB::raw('hits24 + 1'),
    ]);
} else {
    $counter->update([
        'allhosts' => DB::raw('allhosts + 1'),
        'allhits'  => DB::raw('allhits + 1'),
        'dayhosts' => DB::raw('dayhosts + 1'),
        'dayhits'  => DB::raw('dayhits + 1'),
        'hosts24'  => DB::raw('hosts24 + 1'),
        'hits24'   => DB::raw('hits24 + 1'),
    ]);
}

