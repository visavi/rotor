<?php
App::view($config['themes'].'/index');

if (empty($_GET['uz'])) {
    $uz = check($log);
} else {
    $uz = check($_GET['uz']);
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

        $total = DB::run() -> querySingle("SELECT count(*) FROM `comments` WHERE relate_type=?;", ['gallery']);
        $page = App::paginate(App::setting('postgallery'), $total);

        if ($total > 0) {
            $config['newtitle'] = 'Список всех комментариев (Стр. '.$page['current'].')';

            $querycomm = DB::run() -> query("SELECT `comments`.*, `title` FROM `comments` LEFT JOIN `photo` ON `comments`.`relate_id`=`photo`.`id` WHERE relate_type='gallery' ORDER BY comments.`time` DESC LIMIT ".$page['offset'].", ".$config['postgallery'].";");

            while ($data = $querycomm -> fetch()) {

                echo '<div class="b"><i class="fa fa-comment"></i> <b><a href="/gallery/comments?act=viewcomm&amp;gid='.$data['relate_id'].'&amp;cid='.$data['id'].'">'.$data['title'].'</a></b>';
                echo '</div>';


                echo '<div>'.App::bbCode($data['text']).'<br />';
                echo 'Написал: '.profile($data['user']).'</b> <small>('.date_fixed($data['time']).')</small><br />';

                if (is_admin() || empty($config['anonymity'])) {
                    echo '<span class="data">('.$data['brow'].', '.$data['ip'].')</span>';
                }

                echo '</div>';
            }

            App::pagination($page);

        } else {
            show_error('Комментариев еще нет!');
        }
    break;

    ############################################################################################
    ##                                  Вывод комментариев                                    ##
    ############################################################################################
    case 'comments':
        show_title('Список всех комментариев '.nickname($uz));

        $total = DB::run() -> querySingle("SELECT count(*) FROM `comments` WHERE relate_type=? AND `user`=?;", ['gallery', $uz]);
        $page = App::paginate(App::setting('postgallery'), $total);

        if ($total > 0) {
            $config['newtitle'] = 'Список всех комментариев '.nickname($uz).' (Стр. '.$page['current'].')';

            $querycomm = DB::run() -> query("SELECT `c`.*, `title` FROM `comments` c LEFT JOIN `photo` p ON `c`.`relate_id`=`p`.`id` WHERE relate_type=? AND c.`user`=? ORDER BY c.`time` DESC LIMIT ".$page['offset'].", ".$config['postgallery'].";", ['gallery', $uz]);

            while ($data = $querycomm -> fetch()) {

                echo '<div class="b"><i class="fa fa-comment"></i> <b><a href="/gallery/comments?act=viewcomm&amp;gid='.$data['relate_id'].'&amp;cid='.$data['id'].'">'.$data['title'].'</a></b>';

                if (is_admin()) {
                    echo ' — <a href="/gallery/comments?act=del&amp;id='.$data['id'].'&amp;uz='.$uz.'&amp;page='.$page['current'].'&amp;uid='.$_SESSION['token'].'">Удалить</a>';
                }

                echo '</div>';


                echo '<div>'.App::bbCode($data['text']).'<br />';
                echo 'Написал: '.profile($data['user']).'</b> <small>('.date_fixed($data['time']).')</small><br />';

                if (is_admin() || empty($config['anonymity'])) {
                    echo '<span class="data">('.$data['brow'].', '.$data['ip'].')</span>';
                }

                echo '</div>';
            }

            App::pagination($page);

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

        $querycomm = DB::run() -> querySingle("SELECT COUNT(*) FROM `comments` WHERE relate_type=? AND `id`<=? AND `relate_id`=? ORDER BY `time` ASC LIMIT 1;", ['gallery', $cid, $gid]);

        if (!empty($querycomm)) {
            $end = floor(($querycomm - 1) / $config['postgallery']) * $config['postgallery'];

            redirect("/gallery?act=comments&gid=$gid&page=$end");
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
                $photo = DB::run() -> querySingle("SELECT `relate_id` FROM `comments` WHERE relate_type=? AND `id`=?;", ['gallery', $id]);

                if (!empty($photo)) {
                    DB::run() -> query("DELETE FROM `comments` WHERE relate_type=? AND `id`=? AND `relate_id`=?;", ['', $id, $photo]);
                    DB::run() -> query("UPDATE `photo` SET `comments`=`comments`-? WHERE `id`=?;", [1, $photo]);

                    notice('Комментарий успешно удален!');
                    redirect("/gallery/comments?act=comments&uz=$uz&page=$page");
                } else {
                    show_error('Ошибка! Данного комментария не существует!');
                }
            } else {
                show_error('Ошибка! Неверный идентификатор сессии, повторите действие!');
            }
        } else {
            show_error('Ошибка! Удалять комментарии могут только модераторы!');
        }

        echo '<i class="fa fa-arrow-circle-up"></i> <a href="/gallery/comments?act=comments&amp;uz='.$uz.'&amp;page='.$page.'">Вернуться</a><br />';
    break;

endswitch;

echo '<i class="fa fa-arrow-circle-left"></i> <a href="/gallery">В галерею</a><br />';

App::view($config['themes'].'/foot');
