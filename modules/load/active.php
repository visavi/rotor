<?php
App::view($config['themes'].'/index');

$uz = empty($_GET['uz']) ? check($log) : check($_GET['uz']);
$act = isset($_GET['act']) ? check($_GET['act']) : 'files';
$start = isset($_GET['start']) ? abs(intval($_GET['start'])) : 0;

switch ($act):
############################################################################################
##                                      Вывод файлов                                      ##
############################################################################################
case 'files':
    show_title('Список всех файлов');

    echo '<img src="/images/img/document.gif" alt="image" /> ';
    echo '<a href="/load/add">Публикация</a> / ';
    echo '<a href="/load/add?act=waiting">Ожидающие</a> / ';
    echo '<b>Проверенные</b><hr />';

    $total = DB::run() -> querySingle("SELECT count(*) FROM `downs` WHERE `downs_active`=? AND `downs_user`=?;", array(1, $uz));

    if ($total > 0) {
        if ($start >= $total) {
            $start = 0;
        }

        $querydown = DB::run() -> query("SELECT `downs`.*, `cats_name`, folder FROM `downs` LEFT JOIN `cats` ON `downs`.`downs_cats_id`=`cats`.`cats_id` WHERE `downs_active`=? AND `downs_user`=? ORDER BY `downs_time` DESC LIMIT ".$start.", ".$config['downlist'].";", array(1, $uz));

        while ($data = $querydown -> fetch()) {
            $folder = $data['folder'] ? $data['folder'].'/' : '';

            $filesize = (!empty($data['downs_link'])) ? read_file(BASEDIR.'/upload/files/'.$folder.$data['downs_link']) : 0;

            echo '<div class="b"><img src="/images/img/zip.gif" alt="image" /> ';
            echo '<b><a href="/load/down?act=view&amp;id='.$data['downs_id'].'">'.$data['downs_title'].'</a></b> ('.$filesize.')</div>';

            echo '<div>Категория: <a href="/load/down?cid='.$data['downs_cats_id'].'">'.$data['cats_name'].'</a><br />';
            echo 'Скачиваний: '.$data['downs_load'].'<br />';
            echo '<a href="/load/down?act=comments&amp;id='.$data['downs_id'].'">Комментарии</a> ('.$data['downs_comments'].') ';
            echo '<a href="/load/down?act=end&amp;id='.$data['downs_id'].'">&raquo;</a></div>';
        }

        page_strnavigation('/load/active?act=files&amp;uz='.$uz.'&amp;', $config['downlist'], $start, $total);
    } else {
        show_error('Опубликованных файлов не найдено!');
    }
break;

############################################################################################
##                                     Вывод комментарий                                  ##
############################################################################################
case 'comments':
    show_title('Список всех комментариев');

    $total = DB::run() -> querySingle("SELECT count(*) FROM `commload` WHERE `commload_author`=?;", array($uz));

    if ($total > 0) {
        if ($start >= $total) {
            $start = 0;
        }

        $is_admin = is_admin();

        $querypost = DB::run() -> query("SELECT `commload`.*, `downs_title`, `downs_comments` FROM `commload` LEFT JOIN `downs` ON `commload`.`commload_down`=`downs`.`downs_id` WHERE `commload_author`=? ORDER BY `commload_time` DESC LIMIT ".$start.", ".$config['downlist'].";", array($uz));

        while ($data = $querypost -> fetch()) {
            echo '<div class="b">';

            echo '<img src="/images/img/balloon.gif" alt="image" /> <b><a href="/load/active?act=viewcomm&amp;id='.$data['commload_down'].'&amp;cid='.$data['commload_id'].'">'.$data['downs_title'].'</a></b> ('.$data['downs_comments'].')';

            if ($is_admin) {
                echo ' — <a href="/load/active?act=del&amp;id='.$data['commload_id'].'&amp;uz='.$uz.'&amp;start='.$start.'&amp;uid='.$_SESSION['token'].'">Удалить</a>';
            }

            echo '</div>';
            echo '<div>'.bb_code($data['commload_text']).'<br />';

            echo 'Написал: '.nickname($data['commload_author']).' <small>('.date_fixed($data['commload_time']).')</small><br />';

            if ($is_admin || empty($config['anonymity'])) {
                echo '<span class="data">('.$data['commload_brow'].', '.$data['commload_ip'].')</span>';
            }

            echo '</div>';
        }

        page_strnavigation('/load/active?act=comments&amp;uz='.$uz.'&amp;', $config['downlist'], $start, $total);
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

############################################################################################
##                                 Удаление комментариев                                  ##
############################################################################################
case 'del':

    $uid = check($_GET['uid']);
    if (isset($_GET['id'])) {
        $id = abs(intval($_GET['id']));
    } else {
        $id = 0;
    }

    if (is_admin()) {
        if ($uid == $_SESSION['token']) {
            $downs = DB::run() -> querySingle("SELECT `commload_down` FROM `commload` WHERE `commload_id`=?;", array($id));
            if (!empty($downs)) {
                DB::run() -> query("DELETE FROM `commload` WHERE `commload_id`=? AND `commload_down`=?;", array($id, $downs));
                DB::run() -> query("UPDATE `downs` SET `downs_comments`=`downs_comments`-? WHERE `downs_id`=?;", array(1, $downs));

                $_SESSION['note'] = 'Комментарий успешно удален!';
                redirect("/load/active?act=comments&uz=$uz&start=$start");
            } else {
                show_error('Ошибка! Данного комментария не существует!');
            }
        } else {
            show_error('Ошибка! Неверный идентификатор сессии, повторите действие!');
        }
    } else {
        show_error('Ошибка! Удалять комментарии могут только модераторы!');
    }

    echo '<i class="fa fa-arrow-circle-left"></i> <a href="/load/active?act=comments&amp;uz='.$uz.'&amp;start='.$start.'">Вернуться</a><br />';
break;

endswitch;

echo '<i class="fa fa-arrow-circle-up"></i> <a href="/load">Категории</a><br />';

App::view($config['themes'].'/foot');
