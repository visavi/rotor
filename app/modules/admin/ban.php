<?php
view(setting('themes').'/index');

if (isset($_GET['act'])) {
    $act = check($_GET['act']);
} else {
    $act = 'index';
}
if (isset($_POST['uz'])) {
    $uz = check($_POST['uz']);
} elseif (isset($_GET['uz'])) {
    $uz = check($_GET['uz']);
} else {
    $uz = '';
}

if (is_admin([101, 102, 103])) {
    //show_title('Бан/Разбан');

    switch ($action):
    ############################################################################################
    ##                                    Главная страница                                    ##
    ############################################################################################
        case 'index':

            echo '<div class="form">';
            echo 'Логин пользователя:<br>';
            echo '<form method="post" action="/admin/ban?act=edit">';
            echo '<input type="text" name="uz" maxlength="20">';
            echo '<input value="Редактировать" type="submit"></form></div><br>';

            echo 'Введите логин пользователя который необходимо отредактировать<br><br>';
        break;

        ############################################################################################
        ##                                   Редактирование                                       ##
        ############################################################################################
        case 'edit':

            $user = DB::run() -> queryFetch("SELECT * FROM `users` WHERE LOWER(`login`)=? LIMIT 1;", [strtolower($uz)]);

            if (!empty($user)) {
                $uz = $user['login'];

                echo user_gender($user['login']).' <b>Профиль '.profile($user['login']).'</b> '.user_visit($user['login']).'<br><br>';

                if (!empty($user['timelastban']) && !empty($user['reasonban'])) {
                    echo '<div class="form">';
                    echo 'Последний бан: '.dateFixed($user['timelastban'], 'j F Y / H:i').'<br>';
                    echo 'Последняя причина: '.bbCode($user['reasonban']).'<br>';
                    echo 'Забанил: '.profile($user['loginsendban']).'</div><br>';
                }

                $total = DB::run() -> querySingle("SELECT COUNT(*) FROM `banhist` WHERE `user`=?;", [$uz]);

                echo 'Строгих нарушений: <b>'.$user['totalban'].'</b><br>';
                echo '<i class="fa fa-history"></i> <b><a href="/admin/banhist?act=view&amp;uz='.$uz.'">История банов</a></b> ('.$total.')<br><br>';

                if ($user['level'] < 101 || $user['level'] > 105) {
                    if (empty($user['ban']) || $user['timeban'] < SITETIME) {
                        if ($user['totalban'] < 5) {
                            echo '<div class="form">';
                            echo '<form method="post" action="/admin/ban?act=zaban&amp;uz='.$uz.'&amp;uid='.$_SESSION['token'].'">';
                            echo '<b>Время бана:</b><br><input name="bantime"><br>';

                            echo '<input name="bantype" type="radio" value="min" checked="checked"> Минут<br>';
                            echo '<input name="bantype" type="radio" value="chas"> Часов<br>';
                            echo '<input name="bantype" type="radio" value="sut"> Суток<br>';

                            echo '<b>Причина бана:</b><br>';
                            echo '<textarea name="reasonban" cols="25" rows="5"></textarea><br>';

                            $usernote = DB::run() -> queryFetch("SELECT * FROM `note` WHERE `user`=? LIMIT 1;", [$uz]);

                            echo '<b>Заметка:</b><br>';
                            echo '<textarea cols="25" rows="5" name="note">'.$usernote['text'].'</textarea><br>';

                            echo '<input value="Забанить" type="submit"></form></div><br>';

                            echo 'Подсчет нарушений производится при бане более чем на 12 часов<br>';
                            echo 'При общем числе нарушений более пяти, профиль пользователя удаляется<br>';
                            echo 'Максимальное время бана '.round(setting('maxbantime') / 1440).' суток<br>';
                            echo 'Внимание! Постарайтесь как можно подробнее описать причину бана<br><br>';
                        } else {
                            echo '<b><span style="color:#ff0000">Внимание! Пользователь превысил лимит банов</span></b><br>';
                            echo 'Вы можете удалить этот профиль!<br><br>';
                            echo '<i class="fa fa-times"></i> <b><a href="/admin/ban?act=deluser&amp;uz='.$uz.'&amp;uid='.$_SESSION['token'].'">Удалить профиль</a></b><br><br>';
                        }
                    } else {
                        echo '<b><span style="color:#ff0000">Внимание, данный аккаунт заблокирован!</span></b><br>';
                        echo 'До окончания бана: '.formatTime($user['timeban'] - SITETIME).'<br><br>';

                        echo '<i class="fa fa-pencil"></i> <a href="/admin/ban?act=editban&amp;uz='.$uz.'">Изменить</a><br>';
                        echo '<i class="fa fa-arrow-circle-up"></i> <a href="/admin/ban?act=razban&amp;uz='.$uz.'&amp;uid='.$_SESSION['token'].'">Разбанить</a><hr>';
                    }
                } else {
                    showError('Ошибка! Запрещено банить админов и модеров сайта!');
                }
            } else {
                showError('Ошибка! Пользователя с данным логином не существует!');
            }

            echo '<i class="fa fa-arrow-circle-left"></i> <a href="/admin/ban">Вернуться</a><br>';
        break;

        ############################################################################################
        ##                                   Редактирование бана                                  ##
        ############################################################################################
        case 'editban':

            $user = DB::run() -> queryFetch("SELECT * FROM `users` WHERE `login`=? LIMIT 1;", [$uz]);
            if (!empty($user)) {
                echo user_gender($user['login']).' <b>Профиль '.profile($user['login']).'</b> '.user_visit($user['login']).'<br><br>';

                if ($user['level'] < 101 || $user['level'] > 105) {
                    if (!empty($user['ban']) && $user['timeban'] > SITETIME) {
                        if (!empty($user['timelastban'])) {
                            echo 'Последний бан: '.dateFixed($user['timelastban'], 'j F Y / H:i').'<br>';
                            echo 'Забанил: '.profile($user['loginsendban']).'<br>';
                        }
                        echo 'Строгих нарушений: <b>'.$user['totalban'].'</b><br>';
                        echo 'До окончания бана: '.formatTime($user['timeban'] - SITETIME).'<br><br>';

                        if ($user['timeban'] - SITETIME >= 86400) {
                            $type = 'sut';
                            $file_time = round(((($user['timeban'] - SITETIME) / 60) / 60) / 24, 1);
                        } elseif (
                            $user['timeban'] - SITETIME >= 3600) {
                            $type = 'chas';
                            $file_time = round((($user['timeban'] - SITETIME) / 60) / 60, 1);
                        } else {
                            $type = 'min';
                            $file_time = round(($user['timeban'] - SITETIME) / 60);
                        }

                        echo '<div class="form">';
                        echo '<form method="post" action="/admin/ban?act=changeban&amp;uz='.$uz.'&amp;uid='.$_SESSION['token'].'">';
                        echo 'Время бана:<br><input name="bantime" value="'.$file_time.'"><br>';

                        $checked = ($type == 'min') ? ' checked="checked"' : '';
                        echo '<input name="bantype" type="radio" value="min"'.$checked.'> Минут<br>';
                        $checked = ($type == 'chas') ? ' checked="checked"' : '';
                        echo '<input name="bantype" type="radio" value="chas"'.$checked.'> Часов<br>';
                        $checked = ($type == 'sut') ? ' checked="checked"' : '';
                        echo '<input name="bantype" type="radio" value="sut"'.$checked.'> Суток<br>';

                        echo 'Причина бана:<br>';
                        echo '<textarea name="reasonban" cols="25" rows="5">'.$user['reasonban'].'</textarea><br>';

                        echo '<input value="Изменить" type="submit"></form></div><br>';
                    } else {
                        showError('Ошибка! Данный пользователь не забанен!');
                    }
                } else {
                    showError('Ошибка! Запрещено банить админов и модеров сайта!');
                }
            } else {
                showError('Ошибка! Пользователя с данным логином не существует!');
            }

            echo '<i class="fa fa-arrow-circle-left"></i> <a href="/admin/ban?act=edit&amp;uz='.$uz.'">Вернуться</a><br>';
        break;

        ############################################################################################
        ##                                     Изменение бана                                     ##
        ############################################################################################
        case 'changeban':

            $uid = check($_GET['uid']);
            $bantime = abs(round($_POST['bantime'], 1));
            $bantype = check($_POST['bantype']);
            $reasonban = check($_POST['reasonban']);
            $note = check($_POST['note']);

            if ($uid == $_SESSION['token']) {
                $user = DB::run() -> queryFetch("SELECT * FROM `users` WHERE `login`=? LIMIT 1;", [$uz]);

                if (!empty($user)) {
                    if (!empty($user['ban']) && $user['timeban'] > SITETIME) {
                        if ($user['level'] < 101 || $user['level'] > 105) {
                            if ($bantype == 'min') {
                                $bantotaltime = $bantime;
                            }
                            if ($bantype == 'chas') {
                                $bantotaltime = round($bantime * 60);
                            }
                            if ($bantype == 'sut') {
                                $bantotaltime = round($bantime * 1440);
                            }

                            if ($bantotaltime > 0) {
                                if ($bantotaltime <= setting('maxbantime')) {
                                    if (utfStrlen($reasonban) >= 5 && utfStrlen($reasonban) <= 1000) {
                                        if (utfStrlen($note) <= 1000) {

                                            DB::run() -> query("UPDATE `users` SET `ban`=?, `timeban`=?, `reasonban`=?, `loginsendban`=? WHERE `login`=? LIMIT 1;", [1, SITETIME + ($bantotaltime * 60), $reasonban, getUsername(), $uz]);

                                            DB::run() -> query("INSERT INTO `banhist` (`user`, `send`, `type`, `reason`, `term`, `time`) VALUES (?, ?, ?, ?, ?, ?);", [$uz, getUsername(), 2, $reasonban, $bantotaltime * 60, SITETIME]);

                                            setFlash('success', 'Данные успешно изменены!');
                                            redirect("/admin/ban?act=edit&uz=$uz");
                                        } else {
                                            showError('Ошибка! Слишком большая заметка, не более 1000 символов!');
                                        }
                                    } else {
                                        showError('Ошибка! Слишком длинная или короткая причина бана!');
                                    }
                                } else {
                                    showError('Ошибка! Максимальное время бана '.round(setting('maxbantime') / 1440).' суток!');
                                }
                            } else {
                                showError('Ошибка! Вы не указали время бана!');
                            }
                        } else {
                            showError('Ошибка! Запрещено банить админов и модеров сайта!');
                        }
                    } else {
                        showError('Ошибка! Данный пользователь не забанен!');
                    }
                } else {
                    showError('Ошибка! Пользователя с данным логином не существует!');
                }
            } else {
                showError('Ошибка! Неверный идентификатор сессии, повторите действие!');
            }

            echo '<i class="fa fa-arrow-circle-left"></i> <a href="/admin/ban?act=editban&amp;uz='.$uz.'">Вернуться</a><br>';
        break;

        ############################################################################################
        ##                                     Бан пользователя                                   ##
        ############################################################################################
        case 'zaban':

            $uid = check($_GET['uid']);
            $bantime = abs(round($_POST['bantime'], 1));
            $bantype = check($_POST['bantype']);
            $reasonban = check($_POST['reasonban']);
            $notice = check($_POST['note']);

            if ($uid == $_SESSION['token']) {
                $user = DB::run() -> queryFetch("SELECT * FROM `users` WHERE `login`=? LIMIT 1;", [$uz]);

                if (!empty($user)) {
                    if (empty($user['ban']) || $user['timeban'] < SITETIME) {
                        if ($user['level'] < 101 || $user['level'] > 105) {
                            if ($bantype == 'min') {
                                $bantotaltime = $bantime;
                            }
                            if ($bantype == 'chas') {
                                $bantotaltime = round($bantime * 60);
                            }
                            if ($bantype == 'sut') {
                                $bantotaltime = round($bantime * 1440);
                            }

                            if ($bantotaltime > 0) {
                                if ($bantotaltime <= setting('maxbantime')) {
                                    if (utfStrlen($reasonban) >= 5 && utfStrlen($reasonban) <= 1000) {
                                        if (utfStrlen($notice) <= 1000) {

                                            if ($bantotaltime > 720) {
                                                $bancount = 1;
                                            } else {
                                                $bancount = 0;
                                            }

                                            DB::run() -> query("UPDATE `users` SET `ban`=?, `timeban`=?, `timelastban`=?, `reasonban`=?, `loginsendban`=?, `totalban`=`totalban`+?, `explainban`=? WHERE `login`=? LIMIT 1;", [1, SITETIME + ($bantotaltime * 60), SITETIME, $reasonban, getUsername(), $bancount, 1, $uz]);

                                            DB::run() -> query("INSERT INTO `banhist` (`user`, `send`, `type`, `reason`, `term`, `time`) VALUES (?, ?, ?, ?, ?, ?);", [$uz, getUsername(), 1, $reasonban, $bantotaltime * 60, SITETIME]);

                                            $note = Note::where('user', $uz)->find_one();

                                            $record = [
                                                'user' => $uz,
                                                'text' => $notice,
                                                'edit' => getUsername(),
                                                'time' => SITETIME,
                                            ];

                                            Note::saveNote($note, $record);

                                            setFlash('success', 'Аккаунт успешно заблокирован!');
                                            redirect("/admin/ban?act=edit&uz=$uz");
                                        } else {
                                            showError('Ошибка! Слишком большая заметка, не более 1000 символов!');
                                        }
                                    } else {
                                        showError('Ошибка! Слишком длинная или короткая причина бана!');
                                    }
                                } else {
                                    showError('Ошибка! Максимальное время бана '.round(setting('maxbantime') / 1440).' суток!');
                                }
                            } else {
                                showError('Ошибка! Вы не указали время бана!');
                            }
                        } else {
                            showError('Ошибка! Запрещено банить админов и модеров сайта!');
                        }
                    } else {
                        showError('Ошибка! Данный аккаунт уже заблокирован!');
                    }
                } else {
                    showError('Ошибка! Пользователя с данным логином не существует!');
                }
            } else {
                showError('Ошибка! Неверный идентификатор сессии, повторите действие!');
            }

            echo '<i class="fa fa-arrow-circle-left"></i> <a href="/admin/ban?act=edit&amp;uz='.$uz.'">Вернуться</a><br>';
        break;

        ############################################################################################
        ##                                    Разбан пользователя                                 ##
        ############################################################################################
        case 'razban':

            $uid = check($_GET['uid']);

            if ($uid == $_SESSION['token']) {
                $user = DB::run() -> queryFetch("SELECT * FROM `users` WHERE `login`=? LIMIT 1;", [$uz]);

                if (!empty($user)) {
                    if ($user['ban'] == 1) {
                        if ($user['totalban'] > 0 && $user['timeban'] > SITETIME + 43200) {
                            $bancount = 1;
                        } else {
                            $bancount = 0;
                        }

                        DB::run() -> query("UPDATE `users` SET `ban`=?, `timeban`=?, `totalban`=`totalban`-?, `explainban`=? WHERE `login`=? LIMIT 1;", [0, 0, $bancount, 0, $uz]);

                        DB::run() -> query("INSERT INTO `banhist` (`user`, `send`, `time`) VALUES (?, ?, ?);", [$uz, getUsername(), SITETIME]);

                        setFlash('success', 'Аккаунт успешно разблокирован!');
                        redirect("/admin/ban?act=edit&uz=$uz");
                    } else {
                        showError('Ошибка! Данный аккаунт уже разблокирован!');
                    }
                } else {
                    showError('Ошибка! Пользователя с данным логином не существует!');
                }
            } else {
                showError('Ошибка! Неверный идентификатор сессии, повторите действие!');
            }

            echo '<i class="fa fa-arrow-circle-left"></i> <a href="/admin/ban?act=edit&amp;uz='.$uz.'">Вернуться</a><br>';
        break;

        ############################################################################################
        ##                                   Удаление пользователя                                ##
        ############################################################################################
        case 'deluser':

            $uid = check($_GET['uid']);

            if ($uid == $_SESSION['token']) {
                $user = DB::run() -> queryFetch("SELECT * FROM `users` WHERE `login`=? LIMIT 1;", [$uz]);

                if (!empty($user)) {
                    if ($user['totalban'] >= 5) {
                        if ($user['level'] < 101 || $user['level'] > 105) {

                            $blackmail = DB::run() -> querySingle("SELECT `id` FROM `blacklist` WHERE `type`=? AND `value`=? LIMIT 1;", [1, $user['email']]);
                            if (empty($blackmail) && !empty($user['email'])) {
                                DB::run() -> query("INSERT INTO `blacklist` (`type`, `value`, `user`, `time`) VALUES (?, ?, ?, ?);", [1, $user['email'], getUsername(), SITETIME]);
                            }

                            $blacklogin = DB::run() -> querySingle("SELECT `id` FROM `blacklist` WHERE `type`=? AND `value`=? LIMIT 1;", [2, strtolower($user['login'])]);
                            if (empty($blacklogin)) {
                                DB::run() -> query("INSERT INTO `blacklist` (`type`, `value`, `user`, `time`) VALUES (?, ?, ?, ?);", [2, $user['login'], getUsername(), SITETIME]);
                            }

                            delete_album($uz);
                            delete_users($uz);

                            echo 'Данные занесены в черный список!<br>';
                            echo '<i class="fa fa-check"></i> <b>Профиль пользователя успешно удален!</b><br><br>';
                        } else {
                            showError('Ошибка! Запрещено банить админов и модеров сайта!');
                        }
                    } else {
                        showError('Ошибка! У пользователя менее 5 нарушений, удаление невозможно!');
                    }
                } else {
                    showError('Ошибка! Пользователя с данным логином не существует!');
                }
            } else {
                showError('Ошибка! Неверный идентификатор сессии, повторите действие!');
            }

            echo'<i class="fa fa-arrow-circle-left"></i> <a href="/admin/ban">Вернуться</a><br>';
        break;

    endswitch;

    echo '<i class="fa fa-wrench"></i> <a href="/admin">В админку</a><br>';

} else {
    redirect("/");
}

view(setting('themes').'/foot');
