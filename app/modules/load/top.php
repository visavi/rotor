<?php
App::view(Setting::get('themes').'/index');

$sort = isset($_GET['sort']) ? check($_GET['sort']) : 'loads';

switch ($sort) {
    case 'rated': $order = 'rated';
        break;
    case 'comm': $order = 'comments';
        break;
    default: $order = 'loads';
}
############################################################################################
##                                       Топ тем                                          ##
############################################################################################
//show_title('Топ популярных файлов');

echo 'Сортировать: ';

if ($order == 'loads') {
    echo '<b>Скачивания</b> / ';
} else {
    echo '<a href="/load/top?sort=loads">Скачивания</a> / ';
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

echo '<hr>';

$total = DB::run() -> querySingle("SELECT count(*) FROM `downs` WHERE `active`=?;", [1]);
$page = App::paginate(Setting::get('downlist'), $total);

if ($total > 0) {

    $querydown = DB::run() -> query("SELECT `downs`.*, `name`, folder FROM `downs` LEFT JOIN `cats` ON `downs`.`category_id`=`cats`.`id` WHERE `active`=? ORDER BY ".$order." DESC LIMIT ".$page['offset'].", ".Setting::get('downlist').";", [1]);

    while ($data = $querydown -> fetch()) {
        $folder = $data['folder'] ? $data['folder'].'/' : '';

        $filesize = (!empty($data['link'])) ? read_file(HOME.'/uploads/files/'.$folder.$data['link']) : 0;

        echo '<div class="b"><i class="fa fa-file-o"></i> ';
        echo '<b><a href="/load/down?act=view&amp;id='.$data['id'].'">'.$data['title'].'</a></b> ('.$filesize.')</div>';

        echo '<div>Категория: <a href="/load/down?cid='.$data['category_id'].'">'.$data['name'].'</a><br>';
        echo 'Скачиваний: '.$data['loads'].'<br>';
        $rating = (!empty($data['rated'])) ? round($data['rating'] / $data['rated'], 1) : 0;

        echo 'Рейтинг: <b>'.$rating.'</b> (Голосов: '.$data['rated'].')<br>';
        echo '<a href="/load/down?act=comments&amp;id='.$data['id'].'">Комментарии</a> ('.$data['comments'].') ';
        echo '<a href="/load/down?act=end&amp;id='.$data['id'].'">&raquo;</a></div>';
    }

    App::pagination($page);
} else {
    App::showError('Опубликованных файлов еще нет!');
}

echo '<i class="fa fa-arrow-circle-up"></i> <a href="/load">Категории</a><br>';

App::view(Setting::get('themes').'/foot');
