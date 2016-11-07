<?php
App::view($config['themes'].'/index');

$start = (isset($_GET['start'])) ? abs(intval($_GET['start'])) : 0;
$sort = isset($_GET['sort']) ? check($_GET['sort']) : 'load';

switch ($sort) {
    case 'rated': $order = 'rated';
        break;
    case 'comm': $order = 'comments';
        break;
    default: $order = 'load';
}
############################################################################################
##                                       Топ тем                                          ##
############################################################################################
show_title('Топ популярных файлов');

echo 'Сортировать: ';

if ($order == 'load') {
    echo '<b>Скачивания</b> / ';
} else {
    echo '<a href="/load/top?sort=load">Скачивания</a> / ';
}

if ($order == 'rated') {
    echo '<b>Оценки</b> / ';
} else {
    echo '<a href="/load/top?sort=rated">Оценки</a> / ';
}

if ($order == 'comments') {
    echo '<b>Комментарии</b>';
} else {
    echo '<a href="/load/top?sort=comm">Комментарии</a>';
}

echo '<hr />';

$total = DB::run() -> querySingle("SELECT count(*) FROM `downs` WHERE `active`=?;", [1]);

if ($total > 0) {
    if ($start >= $total) {
        $start = 0;
    }

    $querydown = DB::run() -> query("SELECT `downs`.*, `id`, `name`, folder FROM `downs` LEFT JOIN `cats` ON `downs`.`cats_id`=`cats`.`id` WHERE `active`=? ORDER BY ".$order." DESC LIMIT ".$start.", ".$config['downlist'].";", [1]);

    while ($data = $querydown -> fetch()) {
        $folder = $data['folder'] ? $data['folder'].'/' : '';

        $filesize = (!empty($data['link'])) ? read_file(HOME.'/upload/files/'.$folder.$data['link']) : 0;

        echo '<div class="b"><i class="fa fa-file-o"></i> ';
        echo '<b><a href="/load/down?act=view&amp;id='.$data['id'].'">'.$data['title'].'</a></b> ('.$filesize.')</div>';

        echo '<div>Категория: <a href="/load/down?cid='.$data['id'].'">'.$data['name'].'</a><br />';
        echo 'Скачиваний: '.$data['load'].'<br />';
        $raiting = (!empty($data['rated'])) ? round($data['raiting'] / $data['rated'], 1) : 0;

        echo 'Рейтинг: <b>'.$raiting.'</b> (Голосов: '.$data['rated'].')<br />';
        echo '<a href="/load/down?act=comments&amp;id='.$data['id'].'">Комментарии</a> ('.$data['comments'].') ';
        echo '<a href="/load/down?act=end&amp;id='.$data['id'].'">&raquo;</a></div>';
    }

    page_strnavigation('/load/top?sort='.$sort.'&amp;', $config['downlist'], $start, $total);
} else {
    show_error('Опубликованных файлов еще нет!');
}

echo '<i class="fa fa-arrow-circle-up"></i> <a href="/load">Категории</a><br />';

App::view($config['themes'].'/foot');
