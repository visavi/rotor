<?php
view(setting('themes').'/index');

if (isset($_GET['act'])) {
    $act = check($_GET['act']);
} else {
    $act = 'index';
}
$page = abs(intval(Request::input('page', 1)));

if (isAdmin([101, 102, 103])) {
    //show_title('Ожидающие регистрации');

    switch ($action):
    ############################################################################################
    ##                                    Главная страница                                    ##
    ############################################################################################
        case "index":

            if (setting('regkeys') == 0) {
                echo '<i class="fa fa-exclamation-circle"></i> <b><span style="color:#ff0000">Подтверждение регистрации отключено!</span></b><br><br>';
            }
            if (setting('regkeys') == 1) {
                echo '<i class="fa fa-exclamation-circle"></i> <b><span style="color:#ff0000">Включено автоматическое подтверждение регистраций!</span></b><br><br>';
            }
            if (setting('regkeys') == 2) {
                echo '<i class="fa fa-exclamation-circle"></i> <b><span style="color:#ff0000">Включена модерация регистраций!</span></b><br><br>';
            }
            // --------------- Удаление не подтвердивших регистрацию -----------//
            if (setting('regkeys') == 1) {
                $querydeluser = DB::run() -> query("SELECT `login` FROM `users` WHERE `confirmreg`>? AND `joined`<?;", [0, SITETIME-86400]);
                $arrdelusers = $querydeluser -> fetchAll(PDO::FETCH_COLUMN);

                $deltotal = count($arrdelusers);

                if ($deltotal > 0) {
                    echo 'Автоматически удалено аккаунтов: <b>'.$deltotal.'</b><br>';

                    foreach($arrdelusers as $key => $value) {
                        if ($key == 0) {
                            $comma = '';
                        } else {
                            $comma = ', ';
                        }
                        echo $comma.'<b>'.$value.'</b>';

                        deleteAlbum($value);
                        deleteUser($value);
                    }
                    echo '<br><br>';
                }
            }
            // --------------------------------------------------------//
            $total = DB::run() -> querySingle("SELECT count(*) FROM `users` WHERE `confirmreg`>?;", [0]);
            $page = paginate(setting('reglist'), $total);

            if ($total > 0) {

                $queryusers = DB::run() -> query("SELECT * FROM `users` WHERE `confirmreg`>? ORDER BY `joined` DESC LIMIT ".$page['offset'].", ".setting('reglist').";", [0]);

                echo '<form action="/admin/reglist?act=choice&amp;page='.$page['current'].'&amp;uid='.$_SESSION['token'].'" method="post">';

                while ($data = $queryusers -> fetch()) {
                    if (empty($data['email'])) {
                        $data['email'] = 'Не указан';
                    }

                    echo '<div class="b">';
                    echo '<input type="checkbox" name="arrayusers[]" value="'.$data['login'].'"> ';
                    echo userGender($data['login']).' <b>'.profile($data['login']).'</b>';
                    echo '(email: '.$data['email'].')</div>';

                    echo '<div>Зарегистрирован: '.dateFixed($data['joined']).'</div>';
                }

                echo '<br><select name="choice">';
                echo '<option value="1">Разрешить</option>';
                echo '<option value="2">Запретить</option>';
                echo '</select>';

                echo '<input type="submit" value="Выполнить"></form>';

                pagination($page);

                echo 'Всего ожидающих: <b>'.(int)$total.'</b><br><br>';
            } else {
                showError('Нет пользователей требующих подтверждения регистрации!');
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
                            DB::run() -> query("UPDATE `users` SET `confirmreg`=?, `confirmregkey`=? WHERE `login` IN ('".$arrayusers."');", [0, '']);

                            setFlash('success', 'Выбранные аккаунты успешно одобрены!');
                            redirect("/admin/reglist?page=$page");
                        }
                        // ----------------------------------- Запрет регистрации -------------------------------------//
                        if ($choice == 2) {
                            foreach($arrayusers as $value) {
                                deleteAlbum($value);
                                deleteUser($value);
                            }

                            setFlash('success', 'Выбранные пользователи успешно удалены!');
                            redirect("/admin/reglist?page=$page");
                        }
                    } else {
                        showError('Ошибка! Отсутствуют выбранные пользователи!');
                    }
                } else {
                    showError('Ошибка! Не выбрано действие для пользователей!');
                }
            } else {
                showError('Ошибка! Неверный идентификатор сессии, повторите действие!');
            }

            echo '<i class="fa fa-arrow-circle-left"></i> <a href="/admin/reglist?page='.$page.'">Вернуться</a><br>';
        break;

    endswitch;

    echo '<i class="fa fa-wrench"></i> <a href="/admin">В админку</a><br>';

} else {
    redirect("/");
}

view(setting('themes').'/foot');
