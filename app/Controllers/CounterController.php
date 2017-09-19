<?php

namespace App\Controllers;

use App\Models\Counter24;
use App\Models\Counter31;

class CounterController extends BaseController
{
    /**
     * Главная страница
     */
    public function index()
    {
        $counts24 = Counter24::query()
            ->selectRaw('sum(hosts) as hosts')
            ->selectRaw('sum(hits) as hits')
            ->first();

        $counts31 = Counter31::query()
            ->selectRaw('sum(hosts) as hosts')
            ->selectRaw('sum(hits) as hits')
            ->first();

        $online = statsOnline();
        $count  = statsCounter();

        return view('counter/index', compact('online', 'count', 'counts24', 'counts31'));
    }

    /**
     * Статистика за сутки
     */
    public function day()
    {
        $currhour = date("G", SITETIME);

        $hours = floor((gmmktime(date("H"), 0, 0, date("m"), date("d"), date("Y")) - gmmktime((date("Z") / 3600), 0, 0, 1, 1, 1970)) / 3600);

        $counts = Counter24::query()->orderBy('hour', 'desc')->get();
        $arrhits   = [];
        $arrhosts  = [];
        $hits_data = [];
        $host_data = [];

        foreach ($counts as $val) {
            $arrhits[$val['hour']] = $val['hits'];
            $arrhosts[$val['hour']] = $val['hosts'];
        }

        for ($i = 0, $tekhours = $hours; $i < 24; $tekhours -= 1, $i++) {
            $hits_data[$tekhours] = $arrhits[$tekhours] ?? 0;
            $host_data[$tekhours] = $arrhosts[$tekhours] ?? 0;
        }

        return view('counter/24', compact('hits_data', 'host_data', 'currhour', 'hours'));
    }

    /**
     * Статистика за месяц
     */
    public function month()
    {
        $currday = date("j", SITETIME);

        $days = floor((gmmktime(0, 0, 0, date("m"), date("d"), date("Y")) - gmmktime(0, 0, 0, 1, 1, 1970)) / 86400);

        $counts = Counter31::query()->orderBy('days', 'desc')->get();

        $arrhits = [];
        $arrhosts = [];
        $hits_data = [];
        $host_data = [];

        foreach ($counts as $val) {
            $arrhits[$val['days']] = $val['hits'];
            $arrhosts[$val['days']] = $val['hosts'];
        }

        for ($i = 0, $tekdays = $days; $i < 31; $tekdays -= 1, $i++) {
            $hits_data[$tekdays] = $arrhits[$tekdays] ?? 0;
            $host_data[$tekdays] = $arrhosts[$tekdays] ?? 0;
        }

        return view('counter/31', compact('hits_data', 'host_data', 'currday', 'days'));
    }
}
