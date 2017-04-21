<?php
App::view(App::setting('themes').'/index');

$act = (isset($_GET['act'])) ? check($_GET['act']) : 'index';
$used = (!empty($_GET['used'])) ? 1  : 0;
$page = abs(intval(Request::input('page', 1)));

if (is_admin([101, 102, 103])) {
    //show_title('Приглашения');

switch ($act):
############################################################################################
##                                    Главная страница                                    ##
############################################################################################
case 'index':

    if (empty(App::setting('invite'))) {
        echo '<i class="fa fa-exclamation-circle"></i> <span style="color:#ff0000"><b>Внимание! Регистрация по приглашения выключена!</b></span><br /><br />';
    }

    if (empty($used)){
        echo '<b>Неиспользованные</b> / <a href="/admin/invitations?used=1">Использованные</a><hr />';
    } else {
        echo '<a href="/admin/invitations">Неиспользованные</a> / <b>Использованные</b><hr />';
    }

    $total = DB::run() -> querySingle("SELECT COUNT(*) FROM `invite` WHERE `used`=?;", [$used]);
    $page = App::paginate(App::setting('listinvite'), $total);

    if ($total > 0) {

        $invitations = DB::run() -> query("SELECT * FROM `invite` WHERE `used`=? ORDER BY `time` DESC LIMIT ".$page['offset'].", ".App::setting('listinvite').";", [$used]);

        echo '<form action="/admin/invitations?act=del&amp;used='.$used.'&amp;page='.$page['current'].'&amp;uid='.$_SESSION['token'].'" method="post">';

        while ($data = $invitations -> fetch()) {

            echo '<div class="b"><input type="checkbox" name="del[]" value="'.$data['id'].'" /> <b>'.$data['hash'].'</b></div>';
            echo '<div>Владелец: '.profile($data['user']).'<br />';

            if (!empty($data['invited'])) {
                echo 'Приглашенный: '.profile($data['invited']).'<br />';
            }
            echo 'Создан: '.date_fixed($data['time']).'<br />';
            echo '</div>';
        }

        echo '<br /><input type="submit" value="Удалить выбранное" /></form>';

        App::pagination($page);

        echo 'Всего ключей: <b>'.$total.'</b><br /><br />';

    } else {
        show_error('Приглашений еще нет!');
    }

    echo '<i class="fa fa-check"></i> <a href="/admin/invitations?act=new">Создать ключи</a><br />';
    echo '<i class="fa fa-key"></i> <a href="/admin/invitations?act=list">Список ключей</a><br />';
break;

############################################################################################
##                                     Создание ключей                                    ##
############################################################################################
case 'new':

    echo '<b><big>Генерация новых ключей:</big></b><br />';
    echo '<div class="form">';
    echo '<form action="/admin/invitations?act=generate&amp;uid='.$_SESSION['token'].'" method="post">';
    echo '<select name="keys">';
    echo '<option value="1">1 ключ</option>';
    echo '<option value="2">2 ключа</option>';
    echo '<option value="3">3 ключа</option>';
    echo '<option value="5">5 ключей</option>';
    echo '<option value="10">10 ключей</option>';
    echo '<option value="20">20 ключей</option>';
    echo '<option value="50">50 ключей</option>';
    echo '</select>	';
    echo '<input type="submit" value="Генерировать" /></form></div><br />';

    echo '<b><big>Отправить ключ пользователю:</big></b><br />';
    echo '<div class="form">';
    echo '<form action="/admin/invitations?act=send&amp;uid='.$_SESSION['token'].'" method="post">';
    echo 'Логин пользователя:<br />';
    echo '<input type="text" name="user" /><br />';
    echo '<select name="keys">';
    echo '<option value="1">1 ключ</option>';
    echo '<option value="2">2 ключа</option>';
    echo '<option value="3">3 ключа</option>';
    echo '<option value="4">4 ключа</option>';
    echo '<option value="5">5 ключей</option>';
    echo '</select><br />';
    echo '<input type="submit" value="Отправить" /></form></div><br />';

    if (is_admin([101])){
        echo '<b><big>Рассылка ключей:</big></b><br />';
        echo '<div class="form">';
        echo 'Разослать ключи активным пользователям:<br />';
        echo '<form action="/admin/invitations?act=mailing&amp;uid='.$_SESSION['token'].'" method="post">';
        echo '<input type="submit" value="Разослать" /></form></div><br />';
    }

    echo '<i class="fa fa-arrow-circle-left"></i> <a href="/admin/invitations">Вернуться</a><br />';
break;

############################################################################################
##                                       Список ключей                                    ##
############################################################################################
case 'list':
    $invitations = DB::run() -> query("SELECT hash FROM `invite` WHERE `user`=? AND `used`=? ORDER BY `time` DESC;", [$log, 0]);
    $invite = $invitations -> fetchAll(PDO::FETCH_COLUMN);
    $total = count($invite);

    if ($total > 0){
        echo 'Всего ваших ключей: '.$total.'<br />';
        echo '<textarea cols="25" rows="10">'.implode(', ', $invite).'</textarea><br /><br />';
    } else {
        show_error('Ошибка! Нет ваших пригласительных ключей!');
    }
    echo '<i class="fa fa-arrow-circle-left"></i> <a href="/admin/invitations">Вернуться</a><br />';
break;

############################################################################################
##                                Отправка ключей в приват                                ##
############################################################################################
case 'send':

    $uid = (isset($_GET['uid'])) ? check($_GET['uid']) : '';
    $keys = (isset($_POST['keys'])) ? abs(intval($_POST['keys'])) : 1;
    $user = (isset($_REQUEST['user'])) ? check($_REQUEST['user']) : '';

    if ($uid == $_SESSION['token']) {
        if (user($user)) {

            $dbr = DB::run() -> prepare("INSERT INTO `invite` (hash, `user`, `time`) VALUES (?, ?, ?);");

            $listkeys = [];

            for($i = 0; $i < $keys; $i++) {
                $key = str_random(rand(12, 15));
                $dbr -> execute($key, $user, SITETIME);
                $listkeys[] = $key;
            }

            $text = 'Вы получили пригласительные ключи в количестве '.count($listkeys).'шт.'.PHP_EOL.'Список ключей: '.implode(', ', $listkeys).PHP_EOL.'С помощью этих ключей вы можете пригласить ваших друзей на этот сайт!';
            send_private($user, $log, $text);

            notice('Ключи успешно отправлены!');
            redirect("/admin/invitations");

        } else {
            show_error('Ошибка! Не найден пользователь с заданным логином!');
        }
    } else {
        show_error('Ошибка! Неверный идентификатор сессии, повторите действие!');
    }

    echo '<i class="fa fa-arrow-circle-left"></i> <a href="/admin/invitations?act=new">Вернуться</a><br />';
break;

############################################################################################
##                                Отправка ключей в приват                                ##
############################################################################################
case 'mailing':

    $uid = (isset($_GET['uid'])) ? check($_GET['uid']) : '';

    if ($uid == $_SESSION['token']) {
        if (is_admin([101])){

            $query = DB::run()->query("SELECT `login` FROM `users` WHERE `timelastlogin`>?;", [SITETIME - (86400 * 7)]);
            $users = $query->fetchAll(PDO::FETCH_COLUMN);

            $users = array_diff($users, [$log]);
            $total = count($users);

            // Рассылка сообщений с подготовкой запросов
            if ($total>0){

                $text = 'Поздравляем! Вы получили пригласительный ключ'.PHP_EOL.'Ваш ключ: %s'.PHP_EOL.'С помощью этого ключа вы можете пригласить вашего друга на этот сайт!';

                $updateusers = DB::run() -> prepare("UPDATE `users` SET `newprivat`=`newprivat`+1 WHERE `login`=? LIMIT 1;");
                $insertprivat = DB::run() -> prepare("INSERT INTO `inbox` (`user`, `author`, `text`, `time`) VALUES (?, ?, ?, ?);");
                $dbr = DB::run() -> prepare("INSERT INTO `invite` (hash, `user`, `time`) VALUES (?, ?, ?);");

                foreach ($users as $user){
                    $key = str_random(rand(12, 15));
                    $updateusers -> execute($user);
                    $insertprivat -> execute($user, $log, sprintf($text, $key), SITETIME);
                    $dbr -> execute($key, $user, SITETIME);
                }

                notice('Ключи успешно разосланы! (Отправлено: '.$total.')');
                redirect("/admin/invitations");

            } else {
                show_error('Ошибка! Отсутствуют получатели ключей!');
            }
        } else {
            show_error('Ошибка! Рассылать ключи может только администрация!');
        }
    } else {
        show_error('Ошибка! Неверный идентификатор сессии, повторите действие!');
    }

    echo '<i class="fa fa-arrow-circle-left"></i> <a href="/admin/invitations?act=new">Вернуться</a><br />';
break;
############################################################################################
##                                    Генерация ключей                                    ##
############################################################################################
case 'generate':

    $uid = (isset($_GET['uid'])) ? check($_GET['uid']) : '';
    $keys = (isset($_POST['keys'])) ? abs(intval($_POST['keys'])) : 0;

    if ($uid == $_SESSION['token']) {
        if (!empty($keys)) {

            $dbr = DB::run() -> prepare("INSERT INTO `invite` (hash, `user`, `time`) VALUES (?, ?, ?);");

            for($i = 0; $i < $keys; $i++) {
                $key = str_random(rand(12, 15));
                $dbr -> execute($key, $log, SITETIME);
            }

            notice('Ключи успешно сгенерированы!');
            redirect("/admin/invitations");

        } else {
            show_error('Ошибка! Не указано число генерируемых ключей!');
        }
    } else {
        show_error('Ошибка! Неверный идентификатор сессии, повторите действие!');
    }

    echo '<i class="fa fa-arrow-circle-left"></i> <a href="/admin/invitations?act=new">Вернуться</a><br />';
break;

############################################################################################
##                                    Удаление ключей                                     ##
############################################################################################
case 'del':

    $uid = (isset($_GET['uid'])) ? check($_GET['uid']) : '';
    $del = (isset($_REQUEST['del'])) ? intar($_REQUEST['del']) : 0;

    if ($uid == $_SESSION['token']) {
        if (!empty($del)) {

            $del = implode(',', $del);

            DB::run() -> query("DELETE FROM `invite` WHERE `id` IN (".$del.");");

            notice('Выбранные ключи успешно удалены!');
            redirect("/admin/invitations?used=$used&page=$page");

        } else {
            show_error('Ошибка! Отсутствуют выбранные ключи!');
        }
    } else {
        show_error('Ошибка! Неверный идентификатор сессии, повторите действие!');
    }

    echo '<i class="fa fa-arrow-circle-left"></i> <a href="/admin/invitations">Вернуться</a><br />';
break;

endswitch;

    echo '<i class="fa fa-wrench"></i> <a href="/admin">В админку</a><br />';

} else {
    redirect("/");
}

App::view(App::setting('themes').'/foot');
