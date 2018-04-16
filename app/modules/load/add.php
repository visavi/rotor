<?php

/**
 * Просмотр ожидающих модерации
 */
case 'waiting':

    //show_title('Список ожидающих модерации файлов');

    echo '<i class="fa fa-book"></i> <a href="/load/add">Публикация</a> / ';
    echo '<b>Ожидающие</b> / ';
    echo '<a href="/load/active">Проверенные</a><hr>';

    $total = DB::run() -> querySingle("SELECT count(*) FROM `downs` WHERE `active`=? AND `user`=?;", [0, getUser('login')]);

    if ($total > 0) {
        $querynew = DB::select("SELECT `downs`.*, `name` FROM `downs` LEFT JOIN `cats` ON `downs`.`category_id`=`cats`.`id` WHERE `active`=? AND `user`=? ORDER BY `time` DESC;", [0, getUser('login')]);

        while ($data = $querynew -> fetch()) {
            echo '<div class="b">';

            echo '<i class="fa fa-download"></i> ';

            echo '<b><a href="/load/add?act=view&amp;id='.$data['id'].'">'.$data['title'].'</a></b> ('.dateFixed($data['time']).')</div>';
            echo '<div>';
            echo 'Категория: '.$data['name'].'<br>';
            if (!empty($data['link'])) {
                echo 'Файл: '.$data['link'].' ('.formatFileSize(UPLOADS.'/files/'.$data['link']).')<br>';
            } else {
                echo 'Файл: <span style="color:#ff0000">Не загружен</span><br>';
            }
            if (!empty($data['screen'])) {
                echo 'Скрин: '.$data['screen'].' ('.formatFileSize(UPLOADS.'/files/'.$data['screen']).')<br>';
            } else {
                echo 'Скрин: <span style="color:#ff0000">Не загружен</span><br>';
            }
            echo '</div>';
        }

        echo '<br>';
    } else {
        showError('Ожидающих модерации файлов еще нет!');
    }

    echo '<i class="fa fa-arrow-circle-left"></i> <a href="/load/add">Вернуться</a><br>';
break;
