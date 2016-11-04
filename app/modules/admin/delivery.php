<?php
App::view($config['themes'].'/index');

if (isset($_GET['act'])) {
    $act = check($_GET['act']);
} else {
    $act = 'index';
}

if (is_admin(array(101))) {
    show_title('Рассылка приватных сообщений');

    switch ($act):
    ############################################################################################
    ##                                    Главная страница                                    ##
    ############################################################################################
        case 'index':

            echo '<div class="form">';
            echo '<form action="/admin/delivery?act=send&amp;uid='.$_SESSION['token'].'" method="post">';
            echo '<textarea id="markItUp" cols="25" rows="5" name="msg"></textarea><br />';
            echo 'Отправить:<br />';
            echo '<label><input name="rec" type="radio" value="1" checked="checked" /> В онлайне</label><br />';
            echo '<label><input name="rec" type="radio" value="2" /> Активным</label><br />';
            echo '<label><input name="rec" type="radio" value="3" /> Администрации</label><br />';
            echo '<label><input name="rec" type="radio" value="4" /> Всем пользователям</label><br /><br />';

            echo '<input type="submit" value="Разослать" /></form></div><br />';
        break;


    ############################################################################################
    ##                                        Рассылка                                        ##
    ############################################################################################
        case 'send':

            $msg = check($_POST['msg']);
            $rec = abs(intval($_POST['rec']));
            $uid = check($_GET['uid']);

            if ($uid == $_SESSION['token']) {
                if ($rec>=1 && $rec<=4){
                    if (utf_strlen($msg) >= 5 && utf_strlen($msg) < 1000) {

                        // Рассылка пользователям, которые в онлайне
                        if ($rec==1){
                            $query = DB::run() -> query("SELECT `visit_user` FROM `visit` WHERE `visit_nowtime`>?;", array(SITETIME-600));
                            $arrusers = $query -> fetchAll(PDO::FETCH_COLUMN);
                        }

                        // Рассылка активным пользователям, которые посещали сайт менее недели назад
                        if ($rec==2){
                            $query = DB::run()->query("SELECT `users_login` FROM `users` WHERE `users_timelastlogin`>?;", array(SITETIME - (86400 * 7)));
                            $arrusers = $query->fetchAll(PDO::FETCH_COLUMN);
                        }

                        // Рассылка администрации
                        if ($rec==3){
                            $query = DB::run()->query("SELECT `users_login` FROM `users` WHERE `users_level`>=? AND `users_level`<=?;", array(101, 105));
                            $arrusers = $query->fetchAll(PDO::FETCH_COLUMN);
                        }

                        // Рассылка всем пользователям сайта
                        if ($rec==4){
                            $query = DB::run()->query("SELECT `users_login` FROM `users`;");
                            $arrusers = $query->fetchAll(PDO::FETCH_COLUMN);
                        }

                        $arrusers = array_diff($arrusers, array($log));
                        $total = count($arrusers);

                        // Рассылка сообщений с подготовкой запросов
                        if ($total>0){

                            $updateusers = DB::run() -> prepare("UPDATE `users` SET `users_newprivat`=`users_newprivat`+1 WHERE `users_login`=? LIMIT 1;");
                            $insertprivat = DB::run() -> prepare("INSERT INTO `inbox` (`inbox_user`, `inbox_author`, `inbox_text`, `inbox_time`) VALUES (?, ?, ?, ?);");

                            foreach ($arrusers as $uzval){
                                $updateusers -> execute($uzval);
                                $insertprivat -> execute($uzval, $log, $msg, SITETIME);
                            }

                            notice('Сообщение успешно разослано! (Отправлено: '.$total.')');
                            redirect("/admin/delivery");

                        } else {
                            show_error('Ошибка! Отсутствуют получатели рассылки!');
                        }

                    } else {
                        show_error('Ошибка! Слишком длинный или короткий текст сообщения!');
                    }

                } else {
                    show_error('Ошибка! Вы не выбрали получаетелей рассылки!');
                }

            } else {
                show_error('Ошибка! Неверный идентификатор сессии, повторите действие!');
            }

            echo '<i class="fa fa-arrow-circle-left"></i> <a href="/admin/delivery">Вернуться</a><br />';
        break;

    endswitch;

    echo '<i class="fa fa-wrench"></i> <a href="/admin">В админку</a><br />';

} else {
	redirect('/');
}

App::view($config['themes'].'/foot');
