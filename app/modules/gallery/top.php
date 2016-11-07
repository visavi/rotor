<?php
App::view($config['themes'].'/index');

if (isset($_GET['start'])) {
    $start = abs(intval($_GET['start']));
} else {
    $start = 0;
}
if (isset($_GET['sort'])) {
    $sort = check($_GET['sort']);
} else {
    $sort = 'rated';
}

switch ($sort) {
    case 'rated': $order = 'rating';
        break;
    case 'comm': $order = 'comments';
        break;
    default: $order = 'rating';
}
############################################################################################
##                                       Топ фото                                         ##
############################################################################################
show_title('Топ популярных фотографий');

echo 'Сортировать: ';

if ($order == 'rating') {
    echo '<b><a href="/gallery/top?sort=rated">Оценки</a></b>, ';
} else {
    echo '<a href="/gallery/top?sort=rated">Оценки</a>, ';
}

if ($order == 'comments') {
    echo '<b><a href="/gallery/top?sort=comm">Комментарии</a></b>';
} else {
    echo '<a href="/gallery/top?sort=comm">Комментарии</a>';
}

echo '<hr />';

$total = DB::run() -> querySingle("SELECT count(*) FROM `photo`;");

if ($total > 0) {
    if ($start >= $total) {
        $start = last_page($total, $config['fotolist']);
    }

    $queryphoto = DB::run() -> query("SELECT * FROM `photo` ORDER BY ".$order." DESC LIMIT ".$start.", ".$config['fotolist'].";");

    while ($data = $queryphoto -> fetch()) {

        echo '<div class="b"><i class="fa fa-picture-o"></i> ';
        echo '<b><a href="/gallery?act=view&amp;gid='.$data['id'].'&amp;start='.$start.'">'.$data['title'].'</a></b> ('.read_file(HOME.'/upload/pictures/'.$data['link']).') ('.format_num($data['rating']).')</div>';

        echo '<div><a href="/gallery?act=view&amp;gid='.$data['id'].'&amp;start='.$start.'">'.resize_image('upload/pictures/', $data['link'], $config['previewsize'], ['alt' => $data['title']]).'</a>';

        echo '<br />'.bb_code($data['text']).'<br />';

        echo 'Добавлено: '.profile($data['user']).' ('.date_fixed($data['time']).')<br />';
        echo '<a href="/gallery?act=comments&amp;gid='.$data['id'].'">Комментарии</a> ('.$data['comments'].') ';
        echo '<a href="/gallery?act=end&amp;gid='.$data['id'].'">&raquo;</a>';
        echo '</div>';
    }

    page_strnavigation('/gallery/top?sort='.$sort.'&amp;', $config['fotolist'], $start, $total);
} else {
    show_error('Загруженных фотографий еще нет!');
}

echo '<i class="fa fa-arrow-circle-up"></i> <a href="/gallery">Галерея</a><br />';

App::view($config['themes'].'/foot');
