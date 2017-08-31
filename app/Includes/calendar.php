<?php

use App\Models\News;

$cal_den = dateFixed(SITETIME, "j");
$cal_mon = dateFixed(SITETIME, "m");
$cal_year = dateFixed(SITETIME, "Y");

$array_news = [];
$array_komm = [];

$news = News::whereRaw('EXTRACT(YEAR_MONTH FROM FROM_UNIXTIME(`created_at`))=EXTRACT(YEAR_MONTH FROM NOW())')
    ->get();

foreach($news as $data) {
    $arrday = dateFixed($data['created_at'], 'j');
    $array_news[] = $arrday;
    $array_komm[$arrday] = $data['id'];
}

$calend = make_calendar($cal_mon, $cal_year);

echo '<table><caption><b>'.dateFixed(SITETIME, 'j F Y').'</b></caption>';

echo '<thead><tr>';
echo '<th>Пн</th><th>Вт</th><th>Ср</th><th>Чт</th><th>Пт</th><th><span style="color:#ff6666">Сб</span></th><th><span style="color:#ff6666">Вс</span></th>';
echo '</tr></thead><tbody>';

foreach ($calend as $valned) {
    echo '<tr>';
    foreach ($valned as $keyday => $valday) {
        if ($cal_den == $valday) {
            echo '<td><b><span style="color:#ff0000">'.$valday.'</span></b></td>';
            continue;
        }

        if (in_array($valday, $array_news)) {
            echo '<td><a href="/news/'.$array_komm[$valday].'"><span style="color:#ff0000">'.$valday.'</span></a></td>';
            continue;
        }

        if ($keyday == 5 || $keyday == 6) {
            echo '<td><span style="color:#ff6666">'.$valday.'</span></td>';
            continue;
        }

        echo '<td>'.$valday.'</td>';
    }
    echo '</tr>';
}
echo '</tbody></table>';
