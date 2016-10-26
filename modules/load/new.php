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

    $total = DB::run() -> querySingle("SELECT count(*) FROM `downs` WHERE `downs_active`=?;", array(1));

    if ($total > 0) {
        if ($total > 100) {
            $total = 100;
        }
        if ($start >= $total) {
            $start = 0;
        }

        $querydown = DB::run() -> query("SELECT `downs`.*, `cats_name`, folder FROM `downs` LEFT JOIN `cats` ON `downs`.`downs_cats_id`=`cats`.`cats_id` WHERE `downs_active`=? ORDER BY `downs_time` DESC LIMIT ".$start.", ".$config['downlist'].";", array(1));

        while ($data = $querydown -> fetch()) {
            $folder = $data['folder'] ? $data['folder'].'/' : '';

            $filesize = (!empty($data['downs_link'])) ? read_file(BASEDIR.'/upload/files/'.$folder.$data['downs_link']) : 0;

            echo '<div class="b"><img src="/images/img/zip.gif" alt="image" /> ';
            echo '<b><a href="/load/down?act=view&amp;id='.$data['downs_id'].'">'.$data['downs_title'].'</a></b> ('.$filesize.')</div>';

            echo '<div>Категория: <a href="/load/down?cid='.$data['downs_cats_id'].'">'.$data['cats_name'].'</a><br />';
            echo 'Скачиваний: '.$data['downs_load'].'<br />';
            echo 'Добавил: '.profile($data['downs_user']).' ('.date_fixed($data['downs_time']).')</div>';
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

        $querydown = DB::run() -> query("SELECT `commload`.*, `downs_title`, `downs_comments` FROM `commload` LEFT JOIN `downs` ON `commload`.`commload_down`=`downs`.`downs_id` ORDER BY `commload_time` DESC LIMIT ".$start.", ".$config['downlist'].";");

        while ($data = $querydown -> fetch()) {
            echo '<div class="b">';

            echo '<img src="/images/img/balloon.gif" alt="image" /> <b><a href="/load/new?act=viewcomm&amp;id='.$data['commload_down'].'&amp;cid='.$data['commload_id'].'">'.$data['downs_title'].'</a></b> ('.$data['downs_comments'].')</div>';

            echo '<div>'.bb_code($data['commload_text']).'<br />';

            echo 'Написал: '.profile($data['commload_author']).' <small>('.date_fixed($data['commload_time']).')</small><br />';

            if (is_admin() || empty($config['anonymity'])) {
                echo '<span class="data">('.$data['commload_brow'].', '.$data['commload_ip'].')</span>';
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

    $querycomm = DB::run() -> querySingle("SELECT COUNT(*) FROM `commload` WHERE `commload_id`<=? AND `commload_down`=? ORDER BY `commload_time` ASC LIMIT 1;", array($cid, $id));

    if (!empty($querycomm)) {
        $end = floor(($querycomm - 1) / $config['downlist']) * $config['downlist'];

        redirect("/load/down?act=comments&id=$id&start=$end");
    } else {
        show_error('Ошибка! Комментарий к данному файлу не существует!');
    }
break;

endswitch;

echo '<img src="/images/img/reload.gif" alt="image" /> <a href="/load">Категории</a><br />';

App::view($config['themes'].'/foot');
