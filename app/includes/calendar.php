<?php

$cal_den = date_fixed(SITETIME, "j");
$cal_mon = date_fixed(SITETIME, "m");
$cal_year = date_fixed(SITETIME, "Y");

$array_news = [];
$array_komm = [];

$querynews = DB::run() -> query("SELECT `id`, `time` FROM `news` WHERE EXTRACT(YEAR_MONTH FROM FROM_UNIXTIME(`time`))=EXTRACT(YEAR_MONTH FROM NOW());");

while ($data = $querynews -> fetch()) {
    $arrday = date_fixed($data['time'], 'j');
    $array_news[] = $arrday;
    $array_komm[$arrday] = $data['id'];
}

$calend = make_calendar($cal_mon, $cal_year);

echo '<table><caption><b>'.date_fixed(SITETIME, 'j F Y').'</b></caption>';

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
