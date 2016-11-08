<?php
App::view($config['themes'].'/index');

$act = isset($_GET['act']) ? check($_GET['act']) : 'files';
$start = isset($_GET['start']) ? abs(intval($_GET['start'])) : 0;

switch ($act):
############################################################################################
##                                        Вывод тем                                       ##
############################################################################################
case 'files':
    show_title('Список новых файлов');

    $total = DB::run() -> querySingle("SELECT count(*) FROM `downs` WHERE `active`=?;", [1]);

    if ($total > 0) {
        if ($total > 100) {
            $total = 100;
        }
        if ($start >= $total) {
            $start = 0;
        }

        $querydown = DB::run() -> query("SELECT `downs`.*, `name`, folder FROM `downs` LEFT JOIN `cats` ON `downs`.`category_id`=`cats`.`id` WHERE `active`=? ORDER BY `time` DESC LIMIT ".$start.", ".$config['downlist'].";", [1]);

        while ($data = $querydown -> fetch()) {
            $folder = $data['folder'] ? $data['folder'].'/' : '';

            $filesize = (!empty($data['link'])) ? read_file(HOME.'/upload/files/'.$folder.$data['link']) : 0;

            echo '<div class="b"><i class="fa fa-file-o"></i> ';
            echo '<b><a href="/load/down?act=view&amp;id='.$data['id'].'">'.$data['title'].'</a></b> ('.$filesize.')</div>';

            echo '<div>Категория: <a href="/load/down?cid='.$data['category_id'].'">'.$data['name'].'</a><br />';
            echo 'Скачиваний: '.$data['load'].'<br />';
            echo 'Добавил: '.profile($data['user']).' ('.date_fixed($data['time']).')</div>';
        }

        page_strnavigation('/load/new?act=files&amp;', $config['downlist'], $start, $total);
    } else {
        show_error('Опубликованных файлов еще нет!');
    }
break;

############################################################################################
##                                     Вывод сообщений                                    ##
############################################################################################
case 'comments':
    show_title('Список последних комментариев');

    $total = DB::run() -> querySingle("SELECT count(*) FROM `commload`;");

    if ($total > 0) {
        if ($total > 100) {
            $total = 100;
        }
        if ($start >= $total) {
            $start = 0;
        }

        $querydown = DB::run() -> query("SELECT `commload`.*, `title`, `comments` FROM `commload` LEFT JOIN `downs` ON `commload`.`down`=`downs`.`id` ORDER BY `time` DESC LIMIT ".$start.", ".$config['downlist'].";");

        while ($data = $querydown -> fetch()) {
            echo '<div class="b">';

            echo '<i class="fa fa-comment"></i> <b><a href="/load/new?act=viewcomm&amp;id='.$data['down'].'&amp;cid='.$data['id'].'">'.$data['title'].'</a></b> ('.$data['comments'].')</div>';

            echo '<div>'.bb_code($data['text']).'<br />';

            echo 'Написал: '.profile($data['author']).' <small>('.date_fixed($data['time']).')</small><br />';

            if (is_admin() || empty($config['anonymity'])) {
                echo '<span class="data">('.$data['brow'].', '.$data['ip'].')</span>';
            }

            echo '</div>';
        }

        page_strnavigation('/load/new?act=comments&amp;', $config['downlist'], $start, $total);
    } else {
        show_error('Комментарии не найдены!');
    }
break;

############################################################################################
##                                     Переход к сообщение                                ##
############################################################################################
case 'viewcomm':

    if (isset($_GET['id'])) {
        $id = abs(intval($_GET['id']));
    } else {
        $id = 0;
    }
    if (isset($_GET['cid'])) {
        $cid = abs(intval($_GET['cid']));
    } else {
        $cid = 0;
    }

    $querycomm = DB::run() -> querySingle("SELECT COUNT(*) FROM `commload` WHERE `id`<=? AND `down`=? ORDER BY `time` ASC LIMIT 1;", [$cid, $id]);

    if (!empty($querycomm)) {
        $end = floor(($querycomm - 1) / $config['downlist']) * $config['downlist'];

        redirect("/load/down?act=comments&id=$id&start=$end");
    } else {
        show_error('Ошибка! Комментарий к данному файлу не существует!');
    }
break;

endswitch;

echo '<i class="fa fa-arrow-circle-up"></i> <a href="/load">Категории</a><br />';

App::view($config['themes'].'/foot');
