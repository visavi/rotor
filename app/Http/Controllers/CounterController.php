<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Models\Counter24;
use App\Models\Counter31;
use Illuminate\View\View;

class CounterController extends Controller
{
    /**
     * Главная страница
     */
    public function index(): View
    {
        $count = statsCounter();
        $online = statsOnline();

        $counts31 = [];
        $counters = Counter31::query()
            ->orderByDesc('period')
            ->limit(30)
            ->get()
            ->keyBy('period');

        for ($i = 0; $i < 30; $i++) {
            $curDate = date('Y-m-d 00:00:00', strtotime("-$i day", SITETIME));

            $cnt = $counters->get($curDate);

            $counts31['hits'][] = $cnt->hits ?? 0;
            $counts31['hosts'][] = $cnt->hosts ?? 0;
            $counts31['labels'][] = date('M j', strtotime($curDate));
        }

        $counts24 = [];
        $counters = Counter24::query()
            ->orderByDesc('period')
            ->limit(24)
            ->get()
            ->keyBy('period');

        for ($i = 0; $i < 24; $i++) {
            $curHour = date('Y-m-d H:00:00', strtotime("-$i hour", SITETIME));

            $cnt = $counters->get($curHour);

            $counts24['hits'][] = $cnt->hits ?? 0;
            $counts24['hosts'][] = $cnt->hosts ?? 0;
            $counts24['labels'][] = date('H', strtotime($curHour));
        }

        return view('counters/index', compact('count', 'online', 'counts24', 'counts31'));
    }
}
