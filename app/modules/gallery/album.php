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
##                                  Вывод комментариев                                    ##
############################################################################################
    case 'index':
        show_title('Альбомы пользователей');

        $total = DB::run() -> querySingle("select COUNT(DISTINCT `user`) from `photo`");

        if ($total > 0) {
            if ($start >= $total) {
                $start = last_page($total, $config['photogroup']);
            }

            $page = floor(1 + $start / $config['photogroup']);
            $config['newtitle'] = 'Альбомы пользователей (Стр. '.$page.')';

            $queryphoto = DB::run() -> query("SELECT COUNT(*) AS cnt, SUM(`comments`) AS comments, `user` FROM `photo` GROUP BY `user` ORDER BY cnt DESC LIMIT ".$start.", ".$config['photogroup'].";");

            while ($data = $queryphoto -> fetch()) {

                echo '<i class="fa fa-picture-o"></i> ';
                echo '<b><a href="/gallery/album?act=photo&amp;uz='.$data['user'].'">'.nickname($data['user']).'</a></b> ('.$data['cnt'].' фото / '.$data['comments'].' комм.)<br />';
            }

            page_strnavigation('/gallery/album?', $config['photogroup'], $start, $total);

            echo 'Всего альбомов: <b>'.$total.'</b><br /><br />';

        } else {
            show_error('Альбомов еще нет!');
        }
    break;

    ############################################################################################
    ##                               Просмотр по пользователям                                ##
    ############################################################################################
    case 'photo':

        show_title('Список всех фотографий '.nickname($uz));

        $total = DB::run() -> querySingle("SELECT count(*) FROM `photo` WHERE `user`=?;", [$uz]);

        if ($total > 0) {
            if ($start >= $total) {
                $start = last_page($total, $config['fotolist']);
            }

            $page = floor(1 + $start / $config['fotolist']);
            $config['newtitle'] = 'Список всех фотографий '.nickname($uz).' (Стр. '.$page.')';

            $queryphoto = DB::run() -> query("SELECT * FROM `photo` WHERE `user`=? ORDER BY `time` DESC LIMIT ".$start.", ".$config['fotolist'].";", [$uz]);

            $moder = ($log == $uz) ? 1 : 0;

            while ($data = $queryphoto -> fetch()) {
                echo '<div class="b"><i class="fa fa-picture-o"></i> ';
                echo '<b><a href="/gallery?act=view&amp;gid='.$data['id'].'&amp;start='.$start.'">'.$data['title'].'</a></b> ('.read_file(HOME.'/upload/pictures/'.$data['link']).')<br />';

                if (!empty($moder)) {
                    echo '<a href="/gallery?act=edit&amp;gid='.$data['id'].'&amp;start='.$start.'">Редактировать</a> / ';
                    echo '<a href="/gallery?act=delphoto&amp;gid='.$data['id'].'&amp;start='.$start.'&amp;uid='.$_SESSION['token'].'">Удалить</a>';
                }

                echo '</div><div>';
                echo '<a href="/gallery?act=view&amp;gid='.$data['id'].'&amp;start='.$start.'">'.resize_image('upload/pictures/', $data['link'], $config['previewsize'], ['alt' => $data['title']]).'</a><br />';

                if (!empty($data['text'])){
                    echo bb_code($data['text']).'<br />';
                }

                echo 'Добавлено: '.profile($data['user']).' ('.date_fixed($data['time']).')<br />';
                echo '<a href="/gallery?act=comments&amp;gid='.$data['id'].'">Комментарии</a> ('.$data['comments'].')';
                echo '</div>';
            }

            page_strnavigation('/gallery/album?act=photo&amp;uz='.$uz.'&amp;', $config['fotolist'], $start, $total);

            echo 'Всего фотографий: <b>'.$total.'</b><br /><br />';
        } else {
            show_error('Фотографий в альбоме еще нет!');
        }

        echo '<i class="fa fa-arrow-circle-up"></i> <a href="/gallery/album">Альбомы</a><br />';
    break;

endswitch;

echo '<i class="fa fa-arrow-circle-left"></i> <a href="/gallery">В галерею</a><br />';

App::view($config['themes'].'/foot');
