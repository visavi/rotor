<?php
App::view($config['themes'].'/index');

$start = isset($_GET['start']) ? abs(intval($_GET['start'])) : 0;

show_title('Список свежих загрузок');

$total = DB::run() -> querySingle("SELECT count(*) FROM `downs` WHERE `downs_active`=? AND `downs_time`>?;", array (1, SITETIME-3600 * 120));

if ($total > 0) {
    if ($start >= $total) {
        $start = 0;
    }

    $querydown = DB::run() -> query("SELECT `downs`.*, `cats_name`, folder FROM `downs` LEFT JOIN `cats` ON `downs`.`downs_cats_id`=`cats`.`cats_id` WHERE `downs_active`=? AND `downs_time`>? ORDER BY `downs_time` DESC LIMIT ".$start.", ".$config['downlist'].";", array(1, SITETIME-3600 * 120));

    while ($data = $querydown -> fetch()) {
        $folder = $data['folder'] ? $data['folder'].'/' : '';

        $filesize = (!empty($data['downs_link'])) ? read_file(HOME.'/upload/files/'.$folder.$data['downs_link']) : 0;

        echo '<div class="b">';

        if ($data['downs_time'] >= (SITETIME-3600 * 24)) {
            echo '<i class="fa fa-file-o text-success"></i> ';
        } elseif ($data['downs_time'] >= (SITETIME-3600 * 72)) {
            echo '<i class="fa fa-file-o text-warning"></i> ';
        } else {
            echo '<i class="fa fa-file-o text-danger"></i> ';
        }

        echo '<b><a href="/load/down?act=view&amp;id='.$data['downs_id'].'">'.$data['downs_title'].'</a></b> ('.$filesize.')</div>';

        echo '<div>Категория: <a href="/load/down?cid='.$data['downs_cats_id'].'">'.$data['cats_name'].'</a><br />';
        echo 'Скачиваний: '.$data['downs_load'].'<br />';
        echo '<a href="/load/down?act=comments&amp;id='.$data['downs_id'].'">Комментарии</a> ('.$data['downs_comments'].') ';
        echo '<a href="/load/down?act=end&amp;id='.$data['downs_id'].'">&raquo;</a><br />';
        echo 'Добавлено: '.profile($data['downs_user']).' ('.date_fixed($data['downs_time']).')</div>';
    }

    page_strnavigation('/load/fresh?', $config['downlist'], $start, $total);

    echo '<i class="fa fa-file-o text-success"></i> - Самая свежая загрузка<br />';
    echo '<i class="fa fa-file-o text-warning"></i> - Более дня назад<br />';
    echo '<i class="fa fa-file-o text-danger"></i> - Более 3 дней назад<br /><br />';

    echo 'Всего файлов: <b>'.$total.'</b><br /><br />';
} else {
    show_error('За последние 5 дней загрузок еще нет!');
}

echo '<i class="fa fa-arrow-circle-up"></i> <a href="/load">Категории</a><br />';

App::view($config['themes'].'/foot');
