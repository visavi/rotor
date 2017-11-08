<?php

use App\Models\News;

$date['day']  = dateFixed(SITETIME, "j");
$date['mon']  = dateFixed(SITETIME, "n");
$date['year'] = dateFixed(SITETIME, "Y");
$startMonth   = mktime(0, 0, 0, $date['mon'], 1);

$newsDays = [];
$newsIds  = [];

$news = News::query()->where('created_at', '>', $startMonth)->get();

if ($news->isNotEmpty()) {
    foreach ($news as $data) {
        $curDay           = dateFixed($data->created_at, 'j');
        $newsDays[]       = $curDay;
        $newsIds[$curDay] = $data->id;
    }
}

$calendar = makeCalendar($date['mon'], $date['year']);

view('app/_calendar', compact('calendar', 'date', 'newsDays', 'newsIds'));

