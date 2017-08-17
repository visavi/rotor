<?php
App::view(Setting::get('themes').'/index');

$act = (isset($_GET['act'])) ? check($_GET['act']) : 'index';
$name = (isset($_GET['name'])) ? '[b]' . check($_GET['name']) . '[/b], ' : '';
$page = abs(intval(Request::input('page', 1)));

//show_title('Мини-чат');

if ($act == 'index') {

    echo '<a href="#form">Написать</a>';

    if (is_admin()) {
        echo ' / <a href="/admin/minichat?page=' . $page . '">Управление</a>';
    }
    echo '<hr>';

    // ---------------------------------------------------------------//
    if (! file_exists(STORAGE."/chat/chat.dat")){
        touch(STORAGE."/chat/chat.dat");
    }

    $file = file(STORAGE."/chat/chat.dat");
    $file = array_reverse($file);
    $total = count($file);

    $page = App::paginate(Setting::get('chatpost'), $total);

    if ($total > 0) {

        if (is_user()) {
            // --------------------------генерация анекдота------------------------------------------------//
            if (Setting::get('shutnik') == 1) {
                $anfi = file(APP."/modules/chat/bots/chat_shut.php");
                $an_rand = array_rand($anfi);
                $anshow = trim($anfi[$an_rand]);

                $tifi = file(STORAGE."/chat/chat.dat");
                $tidw = explode("|", end($tifi));

                if (SITETIME > ($tidw[3] + 180) && empty($tidw[6])) {
                    $unifile = unifile(STORAGE."/chat/chat.dat", 9);
                    $antext = no_br($anshow . '|Весельчак||' . SITETIME . '|Opera|127.0.0.2|1|' . $tidw[7] . '|' . $tidw[8] . '|' . $unifile . '|');

                    write_files(STORAGE."/chat/chat.dat", "$antext\r\n");
                }
            }
            // ------------------------------- Ответ на вопрос ----------------------------------//
            if (Setting::get('magnik') == 1) {
            $mmagfi = file(STORAGE."/chat/chat.dat");
            $mmagshow = explode("|", end($mmagfi));

            if ($mmagshow[8] != "" && SITETIME > $mmagshow[7]) {
                $unifile = unifile(STORAGE."/chat/chat.dat", 9);
                $magtext = no_br('На вопрос никто не ответил, правильный ответ был: [b]' . $mmagshow[8] . '[/b]! Следующий вопрос через 1 минуту|Вундер-киндер||' . SITETIME . '|Opera|127.0.0.3|0|' . (SITETIME + 60) . '||' . $unifile . '|');

                write_files(STORAGE."/chat/chat.dat", "$magtext\r\n");
            }
            // ------------------------------  Новый вопрос  -------------------------------//
            $magfi = file(APP."/modules/chat/bots/chat_mag.php");
            $mag_rand = array_rand($magfi);
            $magshow = $magfi[$mag_rand];
            $magstr = explode("|", $magshow);

            if (empty($mmagshow[8]) && SITETIME > $mmagshow[7] && $magstr[0] != "") {
                $strlent = utf_strlen($magstr[1]);

                if ($strlent > 1 && $strlent < 5) {
                $podskazka = "$strlent буквы";
                } else {
                $podskazka = "$strlent букв";
                }

                $unifile = unifile(STORAGE."/chat/chat.dat", 9);
                $magtext = no_br('Вопрос всем: ' . $magstr[0] . ' - (' . $podskazka . ')|Вундер-киндер||' . SITETIME . '|Opera|127.0.0.3|0|' . (SITETIME + 600) . '|' . $magstr[1] . '|' . $unifile . '|');

                write_files(STORAGE."/chat/chat.dat", "$magtext\r\n");
            }
            }
            // ----------------------------  Подключение бота  -----------------------------------------//
            if (Setting::get('botnik') == 1) {
            if (empty($_SESSION['botochat'])) {
                $hellobots = ['Приветик', 'Здравствуй', 'Хай', 'Добро пожаловать', 'Салют', 'Hello', 'Здарова'];
                $hellobots_rand = array_rand($hellobots);
                $hellobots_well = $hellobots[$hellobots_rand];

                $mmagfi = file(STORAGE."/chat/chat.dat");
                $mmagshow = explode("|", end($mmagfi));

                $unifile = unifile(STORAGE."/chat/chat.dat", 9);
                $weltext = no_br($hellobots_well . ', ' . App::getUsername() . '!|Настюха||' . SITETIME . '|SIE-S65|127.0.0.2|0|' . $mmagshow[7] . '|' . $mmagshow[8] . '|' . $unifile . '|');

                write_files(STORAGE."/chat/chat.dat", "$weltext\r\n");

                $_SESSION['botochat'] = 1;
            }
            }

            $countstr = counter_string(STORAGE."/chat/chat.dat");
            if ($countstr >= Setting::get('maxpostchat')) {
            delete_lines(STORAGE."/chat/chat.dat", [0, 1, 2, 3, 4]);
            }
        }

        if ($total < $page['offset'] + Setting::get('chatpost')) {
            $end = $total;
        } else {
            $end = $page['offset'] + Setting::get('chatpost');
        }
        for ($i = $page['offset']; $i < $end; $i++) {
            $data = explode("|", $file[$i]);

            $useronline = user_online($data[1]);
            $useravatars = user_avatars($data[1]);

            if ($data[1] == 'Вундер-киндер') {
                $useravatars = '<img src="/assets/img/chat/mag.gif" alt="image"> ';
                $useronline = '<i class="fa fa-asterisk fa-spin text-success"></i>';
            }
            if ($data[1] == 'Настюха') {
                $useravatars = '<img src="/assets/img/chat/bot.gif" alt="image"> ';
                $useronline = '<i class="fa fa-asterisk fa-spin text-success"></i>';
            }
            if ($data[1] == 'Весельчак') {
                $useravatars = '<img src="/assets/img/chat/shut.gif" alt="image"> ';
                $useronline = '<i class="fa fa-asterisk fa-spin text-success"></i>';
            }

            echo '<div class="b">';
            echo '<div class="img">' . $useravatars . '</div>';

            echo '<b><a href="/chat?name=' . $data[1] . '#form">' . $data[1] . '</a></b>  <small>(' . date_fixed($data[3]) . ')</small><br>';
            echo user_title($data[1]) . ' ' . $useronline . '</div>';
            echo '<div>' . App::bbCode($data[0]) . '<br>';
            if (is_admin()){
                echo '<span class="data">(' . $data[4] . ', ' . $data[5] . ')</span></div>';
            }
        }

        App::pagination($page);

    } else {
        show_error('Сообщений нет, будь первым!');
    }

    if (is_user()) {
        echo '<div class="form" id="form">';
        echo '<form action="/chat?act=add" method="post">';
        echo '<b>Сообщение:</b><br>';
        echo '<textarea id="markItUp" cols="25" rows="5" name="msg">' . $name . '</textarea><br>';
        echo '<input type="submit" value="Добавить"></form></div>';
    } else {
        echo '<div id="form">';
        show_login('Вы не авторизованы, чтобы добавить сообщение, необходимо');
        echo '</div>';
    }
}

############################################################################################
##                                  Добавление сообщения                                  ##
############################################################################################
if ($act == 'add') {

    $msg = check($_POST['msg']);

    //Setting::get('header') = 'Добавление сообщения';
    //Setting::get('newtitle') = 'Мини-чат - Добавление сообщения';

    if (is_user()) {
        if (utf_strlen($msg) > 3 && utf_strlen($msg) < 1000) {

            if (Flood::isFlood(App::getUserId())) {

                $msg = antimat($msg);

                $file = file(STORAGE."/chat/chat.dat");
                $data = explode("|", end($file));

                $unifile = unifile(STORAGE."/chat/chat.dat", 9);

                if (!isset($data[7])) $data[7] = '';
                if (!isset($data[8])) $data[8] = '';

                $text = no_br($msg . '|' . App::getUsername() . '||' . SITETIME . '|' . App::getUserAgent() . '|' . App::getClientIp() . '|0|' . $data[7] . '|' . $data[8] . '|' . $unifile . '|');

                write_files(STORAGE."/chat/chat.dat", "$text\r\n");

                $countstr = counter_string(STORAGE."/chat/chat.dat");
                if ($countstr >= Setting::get('maxpostchat')) {
                    delete_lines(STORAGE."/chat/chat.dat", [0, 1, 2, 3, 4]);
                }

                DB::run() -> query("UPDATE `users` SET `point`=`point`+1, `money`=`money`+5 WHERE `login`=?", [App::getUsername()]);

                // --------------------------------------------------------------------------//
                if (Setting::get('botnik') == 1) {
                    include_once APP."/modules/chat/bots/chat_bot.php";

                    if ($mssg != "") {
                        $unifile = unifile(STORAGE."/chat/chat.dat", 9);
                        $text = no_br($mssg . '|' . $namebots . '||' . SITETIME . '|MOT-V3|L-O-V-E|0|' . $data[7] . '|' . $data[8] . '|' . $unifile . '|');

                        write_files(STORAGE."/chat/chat.dat", "$text\r\n");
                    }
                }
                // --------------------------------------------------------------------------//
                if (Setting::get('magnik') == 1) {
                    if (!empty($data[8]) && stristr(utf_lower($msg), $data[8])) {
                        $unifile = unifile(STORAGE."/chat/chat.dat", 9);
                        $text = no_br('Молодец ' . App::getUsername() . '! Правильный ответ [b]' . $data[8] . '[/b]! Следующий вопрос через 1 минуту|Вундер-киндер||' . SITETIME . '|Opera|127.0.0.3|0|' . (SITETIME + 60) . '||' . $unifile . '|');

                        write_files(STORAGE."/chat/chat.dat", "$text\r\n");
                    }
                }

                App::setFlash('success', 'Сообщение успешно добавлено!');
                App::redirect("/chat");

            } else {
                show_error('Антифлуд! Разрешается отправлять сообщения раз в ' . Flood::getPeriod() . ' секунд!');
            }
        } else {
            show_error('Ошибка, слишком длинное или короткое сообщение!');
        }
    } else {
        show_login('Вы не авторизованы, чтобы добавить сообщение, необходимо');
    }

echo '<i class="fa fa-arrow-circle-left"></i> <a href="/chat">Вернуться</a><br><br>';

}

echo '<a href="/rules">Правила</a> / ';
echo '<a href="/smiles">Смайлы</a> / ';
echo '<a href="/tags">Теги</a><br><br>';

echo '<i class="fa fa-home"></i> <a href="/">На главную</a>';

App::view(Setting::get('themes').'/foot');
