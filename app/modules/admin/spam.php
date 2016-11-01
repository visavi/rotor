<?php
App::view($config['themes'].'/index');

$config['spamlist'] = 10;

if (isset($_GET['act'])) {
    $act = check($_GET['act']);
} else {
    $act = 'forum';
}
if (isset($_GET['start'])) {
    $start = abs(intval($_GET['start']));
} else {
    $start = 0;
}

if (is_admin(array(101, 102, 103))) {
    show_title('Список жалоб');

    switch ($act):
    ############################################################################################
    ##                                         Форум                                          ##
    ############################################################################################
        case 'forum':

            $total = DB::run() -> querySingle("SELECT count(*) FROM `spam` WHERE `spam_key`=?;", array(1));
            $totalguest = DB::run() -> querySingle("SELECT count(*) FROM `spam` WHERE `spam_key`=?;", array(2));
            $totalpriv = DB::run() -> querySingle("SELECT count(*) FROM `spam` WHERE `spam_key`=?;", array(3));
            $totalwall = DB::run() -> querySingle("SELECT count(*) FROM `spam` WHERE `spam_key`=?;", array(4));
            $totalload = DB::run() -> querySingle("SELECT count(*) FROM `spam` WHERE `spam_key`=?;", array(5));
            $totalblog = DB::run() -> querySingle("SELECT count(*) FROM `spam` WHERE `spam_key`=?;", array(6));

            echo '<b>Форум</b> ('.$total.') / <a href="/admin/spam?act=guest">Гостевая ('.$totalguest.')</a> / <a href="/admin/spam?act=privat">Приват ('.$totalpriv.')</a> / <a href="/admin/spam?act=wall">Стена</a> ('.$totalwall.') / <a href="/admin/spam?act=load">Загрузки</a> ('.$totalload.') / <a href="/admin/spam?act=blog">Блоги</a> ('.$totalblog.')<br /><br />';

            if ($total > 0) {
                if ($start >= $total) {
                    $start = 0;
                }

                $queryban = DB::run() -> query("SELECT * FROM `spam` WHERE `spam_key`=? ORDER BY `spam_addtime` DESC LIMIT ".$start.", ".$config['spamlist'].";", array(1));

                echo '<form action="/admin/spam?act=del&amp;ref=forum&amp;start='.$start.'&amp;uid='.$_SESSION['token'].'" method="post">';
                echo '<div class="form">';
                echo '<input type="checkbox" id="all" onchange="var o=this.form.elements;for(var i=0;i&lt;o.length;i++)o[i].checked=this.checked" /> <b><label for="all">Отметить все</label></b>';
                echo '</div>';

                while ($data = $queryban -> fetch()) {
                    echo '<div class="b">';
                    echo '<input type="checkbox" name="del[]" value="'.$data['spam_id'].'" /> ';
                    echo '<i class="fa fa-file-o"></i> <b>'.profile($data['spam_login']).'</b> <small>('.date_fixed($data['spam_time'], "d.m.y / H:i:s").')</small></div>';
                    echo '<div>Сообщение: '.bb_code($data['spam_text']).'<br />';

                    echo '<a href="'.$data['spam_link'].'">Перейти к сообщению</a><br />';
                    echo 'Жалоба: '.profile($data['spam_user']).' ('.date_fixed($data['spam_addtime']).')</div>';
                }
                echo '<br /><input type="submit" value="Удалить выбранное" /></form>';

                page_strnavigation('/admin/spam?act=forum&amp;', $config['spamlist'], $start, $total);

                if (is_admin(array(101, 102))) {
                    echo '<i class="fa fa-times"></i> <a href="/admin/spam?act=clear&amp;uid='.$_SESSION['token'].'">Очистить</a><br />';
                }
            } else {
                show_error('Жалоб еще нет!');
            }
        break;

        ############################################################################################
        ##                                         Гостевая                                       ##
        ############################################################################################
        case 'guest':

            $total = DB::run() -> querySingle("SELECT count(*) FROM `spam` WHERE `spam_key`=?;", array(2));
            $totalforum = DB::run() -> querySingle("SELECT count(*) FROM `spam` WHERE `spam_key`=?;", array(1));
            $totalpriv = DB::run() -> querySingle("SELECT count(*) FROM `spam` WHERE `spam_key`=?;", array(3));
            $totalwall = DB::run() -> querySingle("SELECT count(*) FROM `spam` WHERE `spam_key`=?;", array(4));
            $totalload = DB::run() -> querySingle("SELECT count(*) FROM `spam` WHERE `spam_key`=?;", array(5));
            $totalblog = DB::run() -> querySingle("SELECT count(*) FROM `spam` WHERE `spam_key`=?;", array(6));

            echo '<a href="/admin/spam?act=forum">Форум ('.$totalforum.')</a> / <b>Гостевая</b> ('.$total.') / <a href="/admin/spam?act=privat">Приват ('.$totalpriv.')</a> / <a href="/admin/spam?act=wall">Стена</a> ('.$totalwall.') / <a href="/admin/spam?act=load">Загрузки</a> ('.$totalload.') / <a href="/admin/spam?act=blog">Блоги</a> ('.$totalblog.')<br /><br />';

            if ($total > 0) {
                if ($start >= $total) {
                    $start = 0;
                }

                $queryban = DB::run() -> query("SELECT * FROM `spam` WHERE `spam_key`=? ORDER BY `spam_addtime` DESC LIMIT ".$start.", ".$config['spamlist'].";", array(2));

                echo '<form action="/admin/spam?act=del&amp;ref=guest&amp;start='.$start.'&amp;uid='.$_SESSION['token'].'" method="post">';
                echo '<div class="form">';
                echo '<input type="checkbox" id="all" onchange="var o=this.form.elements;for(var i=0;i&lt;o.length;i++)o[i].checked=this.checked" /> <b><label for="all">Отметить все</label></b>';
                echo '</div>';

                while ($data = $queryban -> fetch()) {
                    echo '<div class="b">';
                    echo '<input type="checkbox" name="del[]" value="'.$data['spam_id'].'" /> ';
                    echo '<i class="fa fa-file-o"></i> <b>'.profile($data['spam_login']).'</b> <small>('.date_fixed($data['spam_time'], "d.m.y / H:i:s").')</small></div>';
                    echo '<div>Сообщение: '.bb_code($data['spam_text']).'<br />';

                    echo '<a href="'.$data['spam_link'].'">Перейти к сообщению</a><br />';
                    echo 'Жалоба: '.profile($data['spam_user']).' ('.date_fixed($data['spam_addtime']).')</div>';
                }
                echo '<br /><input type="submit" value="Удалить выбранное" /></form>';

                page_strnavigation('/admin/spam?act=guest&amp;', $config['spamlist'], $start, $total);

                if (is_admin(array(101, 102))) {
                    echo '<i class="fa fa-times"></i> <a href="/admin/spam?act=clear&amp;uid='.$_SESSION['token'].'">Очистить</a><br />';
                }
            } else {
                show_error('Жалоб еще нет!');
            }
        break;

        ############################################################################################
        ##                                           Приват                                       ##
        ############################################################################################
        case 'privat':

            $total = DB::run() -> querySingle("SELECT count(*) FROM `spam` WHERE `spam_key`=?;", array(3));
            $totalforum = DB::run() -> querySingle("SELECT count(*) FROM `spam` WHERE `spam_key`=?;", array(1));
            $totalguest = DB::run() -> querySingle("SELECT count(*) FROM `spam` WHERE `spam_key`=?;", array(2));
            $totalwall = DB::run() -> querySingle("SELECT count(*) FROM `spam` WHERE `spam_key`=?;", array(4));
            $totalload = DB::run() -> querySingle("SELECT count(*) FROM `spam` WHERE `spam_key`=?;", array(5));
            $totalblog = DB::run() -> querySingle("SELECT count(*) FROM `spam` WHERE `spam_key`=?;", array(6));

            echo '<a href="/admin/spam?act=forum">Форум ('.$totalforum.')</a> / <a href="/admin/spam?act=guest">Гостевая</a> ('.$totalguest.') / <b>Приват ('.$total.')</b> / <a href="/admin/spam?act=wall">Стена</a> ('.$totalwall.') / <a href="/admin/spam?act=load">Загрузки</a> ('.$totalload.') / <a href="/admin/spam?act=blog">Блоги</a> ('.$totalblog.')<br /><br />';

            if ($total > 0) {
                if ($start >= $total) {
                    $start = 0;
                }

                $queryban = DB::run() -> query("SELECT * FROM `spam` WHERE `spam_key`=? ORDER BY `spam_addtime` DESC LIMIT ".$start.", ".$config['spamlist'].";", array(3));

                echo '<form action="/admin/spam?act=del&amp;ref=privat&amp;start='.$start.'&amp;uid='.$_SESSION['token'].'" method="post">';
                echo '<div class="form">';
                echo '<input type="checkbox" id="all" onchange="var o=this.form.elements;for(var i=0;i&lt;o.length;i++)o[i].checked=this.checked" /> <b><label for="all">Отметить все</label></b>';
                echo '</div>';

                while ($data = $queryban -> fetch()) {
                    echo '<div class="b">';
                    echo '<input type="checkbox" name="del[]" value="'.$data['spam_id'].'" /> ';
                    echo '<i class="fa fa-file-o"></i> <b>'.profile($data['spam_login']).'</b> <small>('.date_fixed($data['spam_time'], "d.m.y / H:i:s").')</small></div>';
                    echo '<div>Сообщение: '.bb_code($data['spam_text']).'<br />';

                    echo 'Жалоба: '.profile($data['spam_user']).' ('.date_fixed($data['spam_addtime']).')</div>';
                }
                echo '<br /><input type="submit" value="Удалить выбранное" /></form>';

                page_strnavigation('/admin/spam?act=privat&amp;', $config['spamlist'], $start, $total);

                if (is_admin(array(101, 102))) {
                    echo '<i class="fa fa-times"></i> <a href="/admin/spam?act=clear&amp;uid='.$_SESSION['token'].'">Очистить</a><br />';
                }
            } else {
                show_error('Жалоб еще нет!');
            }
        break;

        ############################################################################################
        ##                                    Стена сообщений                                     ##
        ############################################################################################
        case 'wall':

            $total = DB::run() -> querySingle("SELECT count(*) FROM `spam` WHERE `spam_key`=?;", array(4));
            $totalforum = DB::run() -> querySingle("SELECT count(*) FROM `spam` WHERE `spam_key`=?;", array(1));
            $totalguest = DB::run() -> querySingle("SELECT count(*) FROM `spam` WHERE `spam_key`=?;", array(2));
            $totalpriv = DB::run() -> querySingle("SELECT count(*) FROM `spam` WHERE `spam_key`=?;", array(3));
            $totalload = DB::run() -> querySingle("SELECT count(*) FROM `spam` WHERE `spam_key`=?;", array(5));
            $totalblog = DB::run() -> querySingle("SELECT count(*) FROM `spam` WHERE `spam_key`=?;", array(6));

            echo '<a href="/admin/spam?act=forum">Форум ('.$totalforum.')</a> / <a href="/admin/spam?act=guest">Гостевая</a> ('.$totalguest.') / <a href="/admin/spam?act=privat">Приват</a> ('.$totalpriv.') / <b>Стена</b> ('.$total.') / <a href="/admin/spam?act=load">Загрузки</a> ('.$totalload.') / <a href="/admin/spam?act=blog">Блоги</a> ('.$totalblog.')<br /><br />';

            if ($total > 0) {
                if ($start >= $total) {
                    $start = 0;
                }

                $queryban = DB::run() -> query("SELECT * FROM `spam` WHERE `spam_key`=? ORDER BY `spam_addtime` DESC LIMIT ".$start.", ".$config['spamlist'].";", array(4));

                echo '<form action="/admin/spam?act=del&amp;ref=wall&amp;start='.$start.'&amp;uid='.$_SESSION['token'].'" method="post">';
                echo '<div class="form">';
                echo '<input type="checkbox" id="all" onchange="var o=this.form.elements;for(var i=0;i&lt;o.length;i++)o[i].checked=this.checked" /> <b><label for="all">Отметить все</label></b>';
                echo '</div>';

                while ($data = $queryban -> fetch()) {
                    echo '<div class="b">';
                    echo '<input type="checkbox" name="del[]" value="'.$data['spam_id'].'" /> ';
                    echo '<i class="fa fa-file-o"></i> <b>'.profile($data['spam_login']).'</b> <small>('.date_fixed($data['spam_time'], "d.m.y / H:i:s").')</small></div>';
                    echo '<div>Сообщение: '.bb_code($data['spam_text']).'<br />';

                    echo '<a href="'.$data['spam_link'].'">Перейти к сообщению</a><br />';
                    echo 'Жалоба: '.profile($data['spam_user']).' ('.date_fixed($data['spam_addtime']).')</div>';
                }
                echo '<br /><input type="submit" value="Удалить выбранное" /></form>';

                page_strnavigation('/admin/spam?act=wall&amp;', $config['spamlist'], $start, $total);

                if (is_admin(array(101, 102))) {
                    echo '<i class="fa fa-times"></i> <a href="/admin/spam?act=clear&amp;uid='.$_SESSION['token'].'">Очистить</a><br />';
                }
            } else {
                show_error('Жалоб еще нет!');
            }
        break;

        ############################################################################################
        ##                                Комментарии в Загрузках                                 ##
        ############################################################################################
        case 'load':

            $total = DB::run() -> querySingle("SELECT count(*) FROM `spam` WHERE `spam_key`=?;", array(5));
            $totalforum = DB::run() -> querySingle("SELECT count(*) FROM `spam` WHERE `spam_key`=?;", array(1));
            $totalguest = DB::run() -> querySingle("SELECT count(*) FROM `spam` WHERE `spam_key`=?;", array(2));
            $totalpriv = DB::run() -> querySingle("SELECT count(*) FROM `spam` WHERE `spam_key`=?;", array(3));
            $totalwall = DB::run() -> querySingle("SELECT count(*) FROM `spam` WHERE `spam_key`=?;", array(4));
            $totalblog = DB::run() -> querySingle("SELECT count(*) FROM `spam` WHERE `spam_key`=?;", array(6));

            echo '<a href="/admin/spam?act=forum">Форум ('.$totalforum.')</a> / <a href="/admin/spam?act=guest">Гостевая</a> ('.$totalguest.') / <a href="/admin/spam?act=privat">Приват</a> ('.$totalpriv.') / <a href="/admin/spam?act=wall">Стена</a> ('.$totalwall.') / <b>Загрузки</b> ('.$total.') / <a href="/admin/spam?act=blog">Блоги</a> ('.$totalblog.')<br /><br />';

            if ($total > 0) {
                if ($start >= $total) {
                    $start = 0;
                }

                $queryban = DB::run() -> query("SELECT * FROM `spam` WHERE `spam_key`=? ORDER BY `spam_addtime` DESC LIMIT ".$start.", ".$config['spamlist'].";", array(5));

                echo '<form action="/admin/spam?act=del&amp;ref=load&amp;start='.$start.'&amp;uid='.$_SESSION['token'].'" method="post">';
                echo '<div class="form">';
                echo '<input type="checkbox" id="all" onchange="var o=this.form.elements;for(var i=0;i&lt;o.length;i++)o[i].checked=this.checked" /> <b><label for="all">Отметить все</label></b>';
                echo '</div>';

                while ($data = $queryban -> fetch()) {
                    echo '<div class="b">';
                    echo '<input type="checkbox" name="del[]" value="'.$data['spam_id'].'" /> ';
                    echo '<i class="fa fa-file-o"></i> <b>'.profile($data['spam_login']).'</b> <small>('.date_fixed($data['spam_time'], "d.m.y / H:i:s").')</small></div>';
                    echo '<div>Сообщение: '.bb_code($data['spam_text']).'<br />';

                    echo '<a href="'.$data['spam_link'].'">Перейти к сообщению</a><br />';
                    echo 'Жалоба: '.profile($data['spam_user']).' ('.date_fixed($data['spam_addtime']).')</div>';
                }
                echo '<br /><input type="submit" value="Удалить выбранное" /></form>';

                page_strnavigation('/admin/spam?act=load&amp;', $config['spamlist'], $start, $total);

                if (is_admin(array(101, 102))) {
                    echo '<i class="fa fa-times"></i> <a href="/admin/spam?act=clear&amp;uid='.$_SESSION['token'].'">Очистить</a><br />';
                }
            } else {
                show_error('Жалоб еще нет!');
            }
        break;

        ############################################################################################
        ##                                 Комментарии в блогах                                   ##
        ############################################################################################
        case 'blog':

            $total = DB::run() -> querySingle("SELECT count(*) FROM `spam` WHERE `spam_key`=?;", array(6));
            $totalforum = DB::run() -> querySingle("SELECT count(*) FROM `spam` WHERE `spam_key`=?;", array(1));
            $totalguest = DB::run() -> querySingle("SELECT count(*) FROM `spam` WHERE `spam_key`=?;", array(2));
            $totalpriv = DB::run() -> querySingle("SELECT count(*) FROM `spam` WHERE `spam_key`=?;", array(3));
            $totalwall = DB::run() -> querySingle("SELECT count(*) FROM `spam` WHERE `spam_key`=?;", array(4));
            $totalload = DB::run() -> querySingle("SELECT count(*) FROM `spam` WHERE `spam_key`=?;", array(5));

            echo '<a href="/admin/spam?act=forum">Форум ('.$totalforum.')</a> / <a href="/admin/spam?act=guest">Гостевая</a> ('.$totalguest.') / <a href="/admin/spam?act=privat">Приват</a> ('.$totalpriv.') / <a href="/admin/spam?act=wall">Стена</a> ('.$totalwall.') / <a href="/admin/spam?act=load">Загрузки</a> ('.$totalload.') / <b>Блоги</b> ('.$total.')<br /><br />';

            if ($total > 0) {
                if ($start >= $total) {
                    $start = 0;
                }

                $queryban = DB::run() -> query("SELECT * FROM `spam` WHERE `spam_key`=? ORDER BY `spam_addtime` DESC LIMIT ".$start.", ".$config['spamlist'].";", array(6));

                echo '<form action="/admin/spam?act=del&amp;ref=blog&amp;start='.$start.'&amp;uid='.$_SESSION['token'].'" method="post">';
                echo '<div class="form">';
                echo '<input type="checkbox" id="all" onchange="var o=this.form.elements;for(var i=0;i&lt;o.length;i++)o[i].checked=this.checked" /> <b><label for="all">Отметить все</label></b>';
                echo '</div>';

                while ($data = $queryban -> fetch()) {
                    echo '<div class="b">';
                    echo '<input type="checkbox" name="del[]" value="'.$data['spam_id'].'" /> ';
                    echo '<i class="fa fa-file-o"></i> <b>'.profile($data['spam_login']).'</b> <small>('.date_fixed($data['spam_time'], "d.m.y / H:i:s").')</small></div>';
                    echo '<div>Сообщение: '.bb_code($data['spam_text']).'<br />';

                    echo '<a href="'.$data['spam_link'].'">Перейти к сообщению</a><br />';
                    echo 'Жалоба: '.profile($data['spam_user']).' ('.date_fixed($data['spam_addtime']).')</div>';
                }
                echo '<br /><input type="submit" value="Удалить выбранное" /></form>';

                page_strnavigation('/admin/spam?act=blog&amp;', $config['spamlist'], $start, $total);

                if (is_admin(array(101, 102))) {
                    echo '<i class="fa fa-times"></i> <a href="/admin/spam?act=clear&amp;uid='.$_SESSION['token'].'">Очистить</a><br />';
                }
            } else {
                show_error('Жалоб еще нет!');
            }
        break;

        ############################################################################################
        ##                                 Удаление сообщений                                     ##
        ############################################################################################
        case "del":

            $uid = check($_GET['uid']);
            $ref = check($_GET['ref']);
            if (isset($_POST['del'])) {
                $del = intar($_POST['del']);
            } else {
                $del = 0;
            }

            if ($uid == $_SESSION['token']) {
                if (!empty($del)) {
                    $del = implode(',', $del);

                    DB::run() -> query("DELETE FROM `spam` WHERE `spam_id` IN (".$del.");");

                    $_SESSION['note'] = 'Выбранные жалобы успешно удалены!';
                    redirect("/admin/spam?act=$ref&start=$start");

                } else {
                    show_error('Ошибка! Отсутствуют выбранные жалобы!');
                }
            } else {
                show_error('Ошибка! Неверный идентификатор сессии, повторите действие!');
            }

            echo '<i class="fa fa-arrow-circle-left"></i> <a href="/admin/spam?start='.$start.'">Вернуться</a><br />';
        break;

        ############################################################################################
        ##                                     Очистка жалоб                                      ##
        ############################################################################################
        case 'clear':

            $uid = check($_GET['uid']);

            if ($uid == $_SESSION['token']) {
                if (is_admin(array(101, 102))) {
                    DB::run() -> query("TRUNCATE `spam`;");

                    $_SESSION['note'] = 'Жалобы успешно очищены!';
                    redirect("/admin/spam");

                } else {
                    show_error('Ошибка! Очищать жалобы могут только админы!');
                }
            } else {
                show_error('Ошибка! Неверный идентификатор сессии, повторите действие!');
            }

            echo '<i class="fa fa-arrow-circle-left"></i> <a href="/admin/spam">Вернуться</a><br />';
        break;

    endswitch;

    echo '<i class="fa fa-wrench"></i> <a href="/admin">В админку</a><br />';

} else {
    redirect("/");
}

App::view($config['themes'].'/foot');
