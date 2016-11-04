<?php
App::view($config['themes'].'/index');

if (isset($_GET['act'])) {
    $act = check($_GET['act']);
} else {
    $act = 'index';
}
if (isset($_GET['start'])) {
    $start = abs(intval($_GET['start']));
} else {
    $start = 0;
}

if (is_admin(array(101, 102, 103))) {
    show_title('Ожидающие регистрации');

    switch ($act):
    ############################################################################################
    ##                                    Главная страница                                    ##
    ############################################################################################
        case "index":

            if ($config['regkeys'] == 0) {
                echo '<i class="fa fa-exclamation-circle"></i> <b><span style="color:#ff0000">Подтверждение регистрации отключено!</span></b><br /><br />';
            }
            if ($config['regkeys'] == 1) {
                echo '<i class="fa fa-exclamation-circle"></i> <b><span style="color:#ff0000">Включено автоматическое подтверждение регистраций!</span></b><br /><br />';
            }
            if ($config['regkeys'] == 2) {
                echo '<i class="fa fa-exclamation-circle"></i> <b><span style="color:#ff0000">Включена модерация регистраций!</span></b><br /><br />';
            }
            // --------------- Удаление не подтвердивших регистрацию -----------//
            if ($config['regkeys'] == 1) {
                $querydeluser = DB::run() -> query("SELECT `users_login` FROM `users` WHERE `users_confirmreg`>? AND `users_joined`<?;", array(0, SITETIME-86400));
                $arrdelusers = $querydeluser -> fetchAll(PDO::FETCH_COLUMN);

                $deltotal = count($arrdelusers);

                if ($deltotal > 0) {
                    echo 'Автоматически удалено аккаунтов: <b>'.$deltotal.'</b><br />';

                    foreach($arrdelusers as $key => $value) {
                        if ($key == 0) {
                            $comma = '';
                        } else {
                            $comma = ', ';
                        }
                        echo $comma.'<b>'.$value.'</b>';

                        delete_album($value);
                        delete_users($value);
                    }
                    echo '<br /><br />';
                }
            }
            // --------------------------------------------------------//
            $total = DB::run() -> querySingle("SELECT count(*) FROM `users` WHERE `users_confirmreg`>?;", array(0));

            if ($total > 0) {
                if ($start >= $total) {
                    $start = 0;
                }

                $queryusers = DB::run() -> query("SELECT * FROM `users` WHERE `users_confirmreg`>? ORDER BY `users_joined` DESC LIMIT ".$start.", ".$config['reglist'].";", array(0));

                echo '<form action="/admin/reglist?act=choice&amp;start='.$start.'&amp;uid='.$_SESSION['token'].'" method="post">';

                while ($data = $queryusers -> fetch()) {
                    if (empty($data['users_email'])) {
                        $data['users_email'] = 'Не указан';
                    }

                    echo '<div class="b">';
                    echo '<input type="checkbox" name="arrayusers[]" value="'.$data['users_login'].'" /> ';
                    echo user_gender($data['users_login']).' <b>'.profile($data['users_login']).'</b>';
                    echo '(E-mail: '.$data['users_email'].')</div>';

                    echo '<div>Зарегистрирован: '.date_fixed($data['users_joined']).'</div>';
                }

                echo '<br /><select name="choice">';
                echo '<option value="1">Разрешить</option>';
                echo '<option value="2">Запретить</option>';
                echo '</select>';

                echo '<input type="submit" value="Выполнить" /></form>';

                page_strnavigation('/admin/reglist?', $config['reglist'], $start, $total);

                echo 'Всего ожидающих: <b>'.(int)$total.'</b><br /><br />';
            } else {
                show_error('Нет пользователей требующих подтверждения регистрации!');
            }

        break;

        ############################################################################################
        ##                                        Действие                                        ##
        ############################################################################################
        case "choice":

            $uid = check($_GET['uid']);
            $choice = intval($_POST['choice']);

            if (isset($_POST['arrayusers'])) {
                $arrayusers = check($_POST['arrayusers']);
            } else {
                $arrayusers = '';
            }

            if ($uid == $_SESSION['token']) {
                if (!empty($choice)) {
                    if (!empty($arrayusers)) {
                        // -------------------------------- Разрешение регистрации -------------------------------------//
                        if ($choice == 1) {
                            $arrayusers = implode(',', $arrayusers);
                            DB::run() -> query("UPDATE `users` SET `users_confirmreg`=?, `users_confirmregkey`=? WHERE `users_login` IN ('".$arrayusers."');", array(0, ''));

                            notice('Выбранные аккаунты успешно одобрены!');
                            redirect("/admin/reglist?start=$start");
                        }
                        // ----------------------------------- Запрет регистрации -------------------------------------//
                        if ($choice == 2) {
                            foreach($arrayusers as $value) {
                                delete_album($value);
                                delete_users($value);
                            }

                            notice('Выбранные пользователи успешно удалены!');
                            redirect("/admin/reglist?start=$start");
                        }
                    } else {
                        show_error('Ошибка! Отсутствуют выбранные пользователи!');
                    }
                } else {
                    show_error('Ошибка! Не выбрано действие для пользователей!');
                }
            } else {
                show_error('Ошибка! Неверный идентификатор сессии, повторите действие!');
            }

            echo '<i class="fa fa-arrow-circle-left"></i> <a href="/admin/reglist?start='.$start.'">Вернуться</a><br />';
        break;

    endswitch;

    echo '<i class="fa fa-wrench"></i> <a href="/admin">В админку</a><br />';

} else {
    redirect("/");
}

App::view($config['themes'].'/foot');
