<?php
view(setting('themes').'/index');

$act = (isset($_GET['act'])) ? check($_GET['act']) : 'index';
$uz = (empty($_GET['uz'])) ? check(getUser('login')) : check($_GET['uz']);
$page = abs(intval(Request::input('page', 1)));

//show_title('Стена сообщений');

$queryuser = DB::run() -> querySingle("SELECT `id` FROM `users` WHERE `login`=? LIMIT 1;", [$uz]);
if (!empty($queryuser)) {
    switch ($action):
    ############################################################################################
    ##                                    Главная страница                                    ##
    ############################################################################################
        case 'index':


        break;

        ############################################################################################
        ##                                    Добавление сообщения                                ##
        ############################################################################################
        case 'add':

            $uid = check($_GET['uid']);
            $msg = check($_POST['msg']);

            if (getUser()) {
                if ($uz == getUser('login') || isAdmin() || is_contact($uz, getUser('login'))){
                    if ($uid == $_SESSION['token']) {
                        if (utfStrlen($msg) >= 5 && utfStrlen($msg) < 1000) {
                            $ignorstr = DB::run() -> querySingle("SELECT `id` FROM ignoring WHERE `user`=? AND `name`=? LIMIT 1;", [$uz, getUser('login')]);
                            if (empty($ignorstr)) {
                                if (Flood::isFlood()) {

                                    $msg = antimat($msg);

                                    if ($uz != getUser('login')) {
                                        DB::update("UPDATE `users` SET `newwall`=`newwall`+1 WHERE `login`=?", [$uz]);
                                    }

                                    DB::insert("INSERT INTO `wall` (`user`, `login`, `text`, `time`) VALUES (?, ?, ?, ?);", [$uz, getUser('login'), $msg, SITETIME]);

                                    DB::delete("DELETE FROM `wall` WHERE `user`=? AND `time` < (SELECT MIN(`time`) FROM (SELECT `time` FROM `wall` WHERE `user`=? ORDER BY `time` DESC LIMIT ".setting('wallmaxpost').") AS del);", [$uz, $uz]);

                                    setFlash('success', 'Запись успешно добавлена!');
                                    redirect("/wall?uz=$uz");
                                } else {
                                    showError('Антифлуд! Разрешается отправлять сообщения раз в '.Flood::getPeriod().' секунд!');
                                }
                            } else {
                                showError('Ошибка! Вы внесены в игнор-лист пользователя!');
                            }
                        } else {
                            showError('Ошибка! Слишком длинное или короткое сообщение!');
                        }
                    } else {
                        showError('Ошибка! Неверный идентификатор сессии, повторите действие!');
                    }
                } else {
                    showError('Стена закрыта, писать могут только пользователи из контактов!');
                }
            } else {
                showError('Для добавления сообщения необходимо авторизоваться');
            }

            echo '<i class="fa fa-arrow-circle-left"></i> <a href="/wall?uz='.$uz.'">Вернуться</a><br>';
        break;

        ############################################################################################
        ##                                    Жалоба на спам                                      ##
        ############################################################################################
        case 'spam':

            $uid = check($_GET['uid']);
            $id = abs(intval($_GET['id']));

            if (getUser()) {
                if ($uid == $_SESSION['token']) {
                    $data = DB::run() -> queryFetch("SELECT * FROM `wall` WHERE `user`=? AND `id`=? LIMIT 1;", [getUser('login'), $id]);

                    if (!empty($data)) {
                        $queryspam = DB::run() -> querySingle("SELECT `id` FROM `spam` WHERE relate=? AND `idnum`=? LIMIT 1;", [4, $id]);

                        if (empty($queryspam)) {
                            if (Flood::isFlood()) {
                                DB::insert("INSERT INTO `spam` (relate, `idnum`, `user`, `login`, `text`, `time`, `addtime`, `link`) VALUES (?, ?, ?, ?, ?, ?, ?, ?);", [4, $data['id'], getUser('login'), $data['login'], $data['text'], $data['time'], SITETIME, setting('home').'/wall?uz='.$uz.'&amp;page='.$page]);

                                setFlash('success', 'Жалоба успешно отправлена!');
                                redirect("/wall?uz=$uz&page=$page");
                            } else {
                                showError('Антифлуд! Разрешается жаловаться на спам не чаще чем раз в '.Flood::getPeriod().' секунд!');
                            }
                        } else {
                            showError('Ошибка! Вы уже отправили жалобу на данное сообщение!');
                        }
                    } else {
                        showError('Ошибка! Данное сообщение написано не на вашей стене!');
                    }
                } else {
                    showError('Ошибка! Неверный идентификатор сессии, повторите действие!');
                }
            } else {
                showError('Для отправки жалобы необходимо авторизоваться');
            }

            echo '<i class="fa fa-arrow-circle-left"></i> <a href="/wall?uz='.$uz.'&amp;page='.$page.'">Вернуться</a><br>';
        break;

        ############################################################################################
        ##                                 Пользовательское удаление                              ##
        ############################################################################################
        case 'delete':

            $uid = check($_GET['uid']);
            if (isset($_POST['del'])) {
                $del = intar($_POST['del']);
            } else {
                $del = 0;
            }

            if ($uz == getUser('login')) {
                if ($uid == $_SESSION['token']) {
                    if (!empty($del)) {
                        $del = implode(',', $del);

                        $delcomments = DB::delete("DELETE FROM `wall` WHERE `id` IN (".$del.") AND `user`=?;", [getUser('login')]);

                        setFlash('success', 'Выбранные записи успешно удалены!');
                        redirect("/wall?uz=$uz&page=$page");
                    } else {
                        showError('Ошибка! Отстутствуют выбранные сообщения для удаления!');
                    }
                } else {
                    showError('Ошибка! Неверный идентификатор сессии, повторите действие!');
                }
            } else {
                showError('Ошибка! Нельзя удалять записи на чужой стене!');
            }

            echo '<i class="fa fa-arrow-circle-left"></i> <a href="/wall?uz='.$uz.'&amp;page='.$page.'">Вернуться</a><br>';
        break;

        ############################################################################################
        ##                                 Удаление комментариев                                  ##
        ############################################################################################
        case 'del':

            $uid = check($_GET['uid']);
            if (isset($_POST['del'])) {
                $del = intar($_POST['del']);
            } else {
                $del = 0;
            }

            if (isAdmin()) {
                if ($uid == $_SESSION['token']) {
                    if (!empty($del)) {
                        $del = implode(',', $del);

                        $delcomments = DB::delete("DELETE FROM `wall` WHERE `id` IN (".$del.");");

                        setFlash('success', 'Выбранные записи успешно удалены!');
                        redirect("/wall?uz=$uz&page=$page");
                    } else {
                        showError('Ошибка! Отстутствуют выбранные сообщения для удаления!');
                    }
                } else {
                    showError('Ошибка! Неверный идентификатор сессии, повторите действие!');
                }
            } else {
                showError('Ошибка! Удалять записи могут только модераторы!');
            }

            echo '<i class="fa fa-arrow-circle-left"></i> <a href="/wall?uz='.$uz.'&amp;page='.$page.'">Вернуться</a><br>';
        break;

    endswitch;

} else {
    showError('Ошибка! Пользователь с данным логином  не зарегистрирован!');
}

view(setting('themes').'/foot');
