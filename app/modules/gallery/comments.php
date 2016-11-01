<?php
App::view($config['themes'].'/index');

if (empty($_GET['uz'])) {
    $uz = check($log);
} else {
    $uz = check($_GET['uz']);
}
if (isset($_GET['start'])) {
    $start = abs(intval($_GET['start']));
} else {
    $start = 0;
}
if (isset($_GET['act'])) {
    $act = check($_GET['act']);
} else {
    $act = 'index';
}

switch ($act):
    ############################################################################################
    ##                                   Вывод всех комментариев                              ##
    ############################################################################################
    case 'index':
        show_title('Список всех комментариев');

        $total = DB::run() -> querySingle("SELECT count(*) FROM `commphoto`;");

        if ($total > 0) {
            if ($start >= $total) {
                $start = last_page($total, $config['postgallery']);
            }

            $page = floor(1 + $start / $config['postgallery']);
            $config['newtitle'] = 'Список всех комментариев (Стр. '.$page.')';

            $querycomm = DB::run() -> query("SELECT `commphoto`.*, `photo_title` FROM `commphoto` LEFT JOIN `photo` ON `commphoto`.`commphoto_gid`=`photo`.`photo_id` ORDER BY `commphoto_time` DESC LIMIT ".$start.", ".$config['postgallery'].";");

            while ($data = $querycomm -> fetch()) {

                echo '<div class="b"><i class="fa fa-comment"></i> <b><a href="/gallery/comments?act=viewcomm&amp;gid='.$data['commphoto_gid'].'&amp;cid='.$data['commphoto_id'].'">'.$data['photo_title'].'</a></b>';
                echo '</div>';


                echo '<div>'.bb_code($data['commphoto_text']).'<br />';
                echo 'Написал: '.profile($data['commphoto_user']).'</b> <small>('.date_fixed($data['commphoto_time']).')</small><br />';

                if (is_admin() || empty($config['anonymity'])) {
                    echo '<span class="data">('.$data['commphoto_brow'].', '.$data['commphoto_ip'].')</span>';
                }

                echo '</div>';
            }

            page_strnavigation('/gallery/comments?', $config['postgallery'], $start, $total);

        } else {
            show_error('Комментариев еще нет!');
        }
    break;

    ############################################################################################
    ##                                  Вывод комментариев                                    ##
    ############################################################################################
    case 'comments':
        show_title('Список всех комментариев '.nickname($uz));

        $total = DB::run() -> querySingle("SELECT count(*) FROM `commphoto` WHERE `commphoto_user`=?;", array($uz));

        if ($total > 0) {
            if ($start >= $total) {
                $start = last_page($total, $config['postgallery']);
            }

            $page = floor(1 + $start / $config['postgallery']);
            $config['newtitle'] = 'Список всех комментариев '.nickname($uz).' (Стр. '.$page.')';

            $querycomm = DB::run() -> query("SELECT `commphoto`.*, `photo_title` FROM `commphoto` LEFT JOIN `photo` ON `commphoto`.`commphoto_gid`=`photo`.`photo_id` WHERE `commphoto_user`=? ORDER BY `commphoto_time` DESC LIMIT ".$start.", ".$config['postgallery'].";", array($uz));

            while ($data = $querycomm -> fetch()) {

                echo '<div class="b"><i class="fa fa-comment"></i> <b><a href="/gallery/comments?act=viewcomm&amp;gid='.$data['commphoto_gid'].'&amp;cid='.$data['commphoto_id'].'">'.$data['photo_title'].'</a></b>';

                if (is_admin()) {
                    echo ' — <a href="/gallery/comments?act=del&amp;id='.$data['commphoto_id'].'&amp;uz='.$uz.'&amp;start='.$start.'&amp;uid='.$_SESSION['token'].'">Удалить</a>';
                }

                echo '</div>';


                echo '<div>'.bb_code($data['commphoto_text']).'<br />';
                echo 'Написал: '.profile($data['commphoto_user']).'</b> <small>('.date_fixed($data['commphoto_time']).')</small><br />';

                if (is_admin() || empty($config['anonymity'])) {
                    echo '<span class="data">('.$data['commphoto_brow'].', '.$data['commphoto_ip'].')</span>';
                }

                echo '</div>';
            }

            page_strnavigation('/gallery/comments?act=comments&amp;uz='.$uz.'&amp;', $config['postgallery'], $start, $total);

        } else {
            show_error('Комментариев еще нет!');
        }
    break;

    ############################################################################################
    ##                                     Переход к сообщение                                ##
    ############################################################################################
    case 'viewcomm':

        if (isset($_GET['gid'])) {
            $gid = abs(intval($_GET['gid']));
        } else {
            $gid = 0;
        }
        if (isset($_GET['cid'])) {
            $cid = abs(intval($_GET['cid']));
        } else {
            $cid = 0;
        }

        $querycomm = DB::run() -> querySingle("SELECT COUNT(*) FROM `commphoto` WHERE `commphoto_id`<=? AND `commphoto_gid`=? ORDER BY `commphoto_time` ASC LIMIT 1;", array($cid, $gid));

        if (!empty($querycomm)) {
            $end = floor(($querycomm - 1) / $config['postgallery']) * $config['postgallery'];

            redirect("/gallery?act=comments&gid=$gid&start=$end");
        } else {
            show_error('Ошибка! Комментарий к данному изображению не существует!');
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
                $photo = DB::run() -> querySingle("SELECT `commphoto_gid` FROM `commphoto` WHERE `commphoto_id`=?;", array($id));

                if (!empty($photo)) {
                    DB::run() -> query("DELETE FROM `commphoto` WHERE `commphoto_id`=? AND `commphoto_gid`=?;", array($id, $photo));
                    DB::run() -> query("UPDATE `photo` SET `photo_comments`=`photo_comments`-? WHERE `photo_id`=?;", array(1, $photo));

                    $_SESSION['note'] = 'Комментарий успешно удален!';
                    redirect("/gallery/comments?act=comments&uz=$uz&start=$start");
                } else {
                    show_error('Ошибка! Данного комментария не существует!');
                }
            } else {
                show_error('Ошибка! Неверный идентификатор сессии, повторите действие!');
            }
        } else {
            show_error('Ошибка! Удалять комментарии могут только модераторы!');
        }

        echo '<i class="fa fa-arrow-circle-up"></i> <a href="/gallery/comments?act=comments&amp;uz='.$uz.'&amp;start='.$start.'">Вернуться</a><br />';
    break;

endswitch;

echo '<i class="fa fa-arrow-circle-left"></i> <a href="/gallery">В галерею</a><br />';

App::view($config['themes'].'/foot');
