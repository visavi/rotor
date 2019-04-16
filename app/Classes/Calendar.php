<?php

declare(strict_types=1);

namespace App\Classes;

use App\Models\News;

class Calendar
{
    /**
     * Возвращает календарь
     *
     * @param int $time
     * @return string календарь
     */
    public function getCalendar($time = SITETIME): string
    {
        [$date['day'], $date['month'], $date['year']] = explode('.', dateFixed($time, 'j.n.Y'));
        $date       = array_map('\intval', $date);
        $startMonth = mktime(0, 0, 0, $date['month'], 1);

        $newsDays = [];
        $newsIds  = [];

        $news = News::query()->where('created_at', '>', $startMonth)->get();

        if ($news->isNotEmpty()) {
            foreach ($news as $data) {
                $curDay           = (int) dateFixed($data->created_at, 'j');
                $newsDays[]       = $curDay;
                $newsIds[$curDay] = $data->id;
            }
        }

        $calendar = $this->makeCalendar($date['month'], $date['year']);

        return view('app/_calendar', compact('calendar', 'date', 'time', 'newsDays', 'newsIds'));
    }

    /**
     * Формирует календарь
     *
     * @param  int   $month месяц
     * @param  int   $year  год
     * @return array        сформированный массив
     */
    protected function makeCalendar($month, $year): array
    {
        $wday = date('w', mktime(0, 0, 0, $month, 1, $year));

        if ($wday === 0) {
            $wday = 7;
        }

        $n = - ($wday-2);
        $cal = [];
        for ($y = 0; $y < 6; $y++) {
            $row = [];
            $notEmpty = false;
            for ($x = 0; $x < 7; $x++, $n++) {
                if (checkdate($month, $n, $year)) {
                    $row[] = $n;
                    $notEmpty = true;
                } else {
                    $row[] = null;
                }
            }

            if (! $notEmpty) {
                break;
            }

            $cal[] = $row;
        }
        return $cal;
    }
}
