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
     *
     * @return string календарь
     */
    public function getCalendar($time = SITETIME): string
    {
        [$date['day'], $date['month'], $date['year']] = explode('.', dateFixed($time, 'j.n.Y'));
        $date       = array_map('intval', $date);
        $startMonth = mktime(0, 0, 0, $date['month'], 1, $date['year']);
        $endMonth   = strtotime('+1 month', $startMonth);

        $news = News::query()
            ->where('created_at', '>=', $startMonth)
            ->where('created_at', '<', $endMonth)
            ->get();

        $newsIds  = [];
        if ($news->isNotEmpty()) {
            foreach ($news as $data) {
                $curDay           = dateFixed($data->created_at, 'j');
                $newsIds[$curDay] = $data->id;
            }
        }

        $calendar = $this->makeCalendar($date['month'], $date['year']);

        return view('app/_calendar', compact('calendar', 'date', 'time', 'newsIds'));
    }

    /**
     * Формирует календарь
     *
     * @param int $month месяц
     * @param int $year  год
     *
     * @return array сформированный массив
     */
    protected function makeCalendar($month, $year): array
    {
        $date = date('w', mktime(0, 0, 0, $month, 1, $year));

        if ($date === 0) {
            $date = 7;
        }

        $n = - ($date-2);
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
