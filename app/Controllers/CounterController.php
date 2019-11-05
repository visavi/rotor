<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Models\Counter24;
use App\Models\Counter31;

class CounterController extends BaseController
{
    /**
     * Главная страница
     *
     * @return string
     */
    public function index(): string
    {
        $count  = statsCounter();
        $online = statsOnline();

        $counts31 = [];
        $counters = Counter31::query()
            ->whereRaw('period BETWEEN NOW() - INTERVAL 31 DAY AND NOW()')
            ->orderBy('period', 'desc')
            ->get();

        for ($i = 0; $i <= 30; $i++) {
            $curDate = date('Y-m-d 00:00:00', strtotime("-$i day", SITETIME));

            $cnt = $counters->first(static function($item) use ($curDate) {
                return $item->period === $curDate;
            });

            $counts31['hits'][]   = $cnt->hits ?? 0;
            $counts31['hosts'][]  = $cnt->hosts ?? 0;
            $counts31['labels'][] = date('M j', strtotime($curDate));
        }

        $counts24 = [];
        $counters = Counter24::query()
            ->whereRaw('period BETWEEN NOW() - INTERVAL 25 HOUR AND NOW()')
            ->orderBy('period', 'desc')
            ->get();

        for ($i = 0; $i <= 24; $i++) {
            $curHour = date('Y-m-d H:00:00', strtotime("-$i hour", SITETIME));

            $cnt = $counters->first(static function($item) use ($curHour) {
                return $item->period === $curHour;
            });

            $counts24['hits'][]   = $cnt->hits ?? 0;
            $counts24['hosts'][]  = $cnt->hosts ?? 0;
            $counts24['labels'][] = date('H', strtotime($curHour));
        }

        return view('counters/index', compact('count', 'online', 'counts24', 'counts31'));
    }
}
