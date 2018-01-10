<?php
view(setting('themes').'/index');

if (isset($_GET['act'])) {
    $act = check($_GET['act']);
} else {
    $act = 'index';
}
if (isset($_GET['id'])) {
    $id = abs(intval($_GET['id']));
} else {
    $id = 0;
}
$page = int(Request::input('page', 1));

if (isAdmin([101, 102, 103])) {
    //show_title('Управление голосованием');

    switch ($action):
    ############################################################################################
    ##                                    Главная страница                                    ##
    ############################################################################################
        case 'index':

            $queryvote = DB::select("SELECT * FROM `vote` WHERE `closed`=? ORDER BY `time` DESC;", [0]);
            $votes = $queryvote -> fetchAll();

            if (count($votes) > 0) {
                foreach($votes as $valvote) {
                    echo '<div class="b">';
                    echo '<i class="fa fa-chart-bar"></i> <b><a href="/votes?act=poll&amp;id='.$valvote['id'].'">'.$valvote['title'].'</a></b><br>';
                    echo '<a href="/admin/votes?act=edit&amp;id='.$valvote['id'].'">Изменить</a>';
                    echo ' / <a href="/admin/votes?act=action&amp;do=close&amp;id='.$valvote['id'].'&amp;uid='.$_SESSION['token'].'">Закрыть</a>';

                    if (isAdmin([101])) {
                        echo ' / <a href="/admin/votes?act=del&amp;id='.$valvote['id'].'&amp;uid='.$_SESSION['token'].'" onclick="return confirm(\'Вы подтверждаете удаление голосования?\')">Удалить</a>';
                    }

                    echo '</div>';

                    echo '<div>Создано: '.dateFixed($valvote['time']).'<br>';
                    echo 'Всего голосов: '.$valvote['count'].'</div>';
                }
                echo '<br>';
            } else {
                showError('Открытых голосований еще нет!');
            }

            echo '<i class="fa fa-chart-bar"></i> <a href="/admin/votes?act=new">Создать голосование</a><br>';
            echo '<i class="fa fa-briefcase"></i> <a href="/admin/votes?act=history">История голосований</a><br>';

            if (isAdmin([101])) {
                echo '<i class="fa fa-sync"></i> <a href="/admin/votes?act=rest&amp;uid='.$_SESSION['token'].'">Пересчитать</a><br>';
            }

        break;

        ############################################################################################
        ##                                      Создание                                          ##
        ############################################################################################
        case 'new':

            echo '<div class="form">';
            echo '<form action="/admin/votes?act=add&amp;uid='.$_SESSION['token'].'" method="post">';

            echo 'Вопрос:<br>';
            echo '<input type="text" name="title" size="50" maxlength="100"><br>';
            echo 'Ответ 1:<br><input type="text" name="answer[]" maxlength="50"><br>';
            echo 'Ответ 2:<br><input type="text" name="answer[]" maxlength="50"><br>';
            echo 'Ответ 3:<br><input type="text" name="answer[]" maxlength="50"><br>';
            echo 'Ответ 4:<br><input type="text" name="answer[]" maxlength="50"><br>';
            echo 'Ответ 5:<br><input type="text" name="answer[]" maxlength="50"><br>';
            echo 'Ответ 6:<br><input type="text" name="answer[]" maxlength="50"><br>';
            echo 'Ответ 7:<br><input type="text" name="answer[]" maxlength="50"><br>';
            echo 'Ответ 8:<br><input type="text" name="answer[]" maxlength="50"><br>';
            echo 'Ответ 9:<br><input type="text" name="answer[]" maxlength="50"><br>';
            echo 'Ответ 10:<br><input type="text" name="answer[]" maxlength="50"><br>';
            echo '<input type="submit" value="Создать"></form></div><br>';

            echo '<i class="fa fa-arrow-circle-left"></i> <a href="/admin/votes">Вернуться</a><br>';
        break;

        ############################################################################################
        ##                                      Создание                                          ##
        ############################################################################################
        case 'add':

            $uid = check($_GET['uid']);
            $title = check($_POST['title']);
            $answer = check($_POST['answer']);

            if ($uid == $_SESSION['token']) {
                if (utfStrlen($title) >= 3 && utfStrlen($title) <= 100) {
                    $answer = array_diff($answer, ['']);

                    if (count($answer) > 1) {
                        DB::insert("INSERT INTO `vote` (`title`, `time`) VALUES (?, ?);", [$title, SITETIME]);
                        $lastid = DB::run() -> lastInsertId();

                        $dbr = DB::run() -> prepare("INSERT INTO `voteanswer` (`vote_id`, `answer`) VALUES (?, ?);");

                        foreach ($answer as $data) {
                            $dbr -> execute($lastid, $data);
                        }

                        setFlash('success', 'Голосование успешно создано!');
                        redirect("/admin/votes");
                    } else {
                        showError('Ошибка! Необходимо минимум 2 варианта ответов!');
                    }
                } else {
                    showError('Ошибка! Слишком длинный или короткий вопрос (от 3 до 100 символов)!');
                }
            } else {
                showError('Ошибка! Неверный идентификатор сессии, повторите действие!');
            }

            echo '<i class="fa fa-arrow-circle-left"></i> <a href="/admin/votes?act=new">Вернуться</a><br>';
            echo '<i class="fa fa-chart-bar"></i> <a href="/admin/votes">К голосованиям</a><br>';
        break;

        ############################################################################################
        ##                                   Редактирование                                       ##
        ############################################################################################
        case 'edit':

            $votes = DB::run() -> queryFetch("SELECT * FROM `vote` WHERE `id`=? LIMIT 1;", [$id]);

            if (!empty($votes)) {
                echo '<div class="form">';
                echo '<form action="/admin/votes?act=change&amp;id='.$id.'&amp;uid='.$_SESSION['token'].'" method="post">';

                echo 'Вопрос:<br>';
                echo '<input type="text" name="title" size="50" maxlength="100" value="'.$votes['title'].'"><br>';

                $queryanswer = DB::select("SELECT * FROM `voteanswer` WHERE `vote_id`=? ORDER BY `id`;", [$id]);
                $answer = $queryanswer -> fetchAll();

                for ($i = 0; $i < 10; $i++) {
                    if (!empty($answer[$i])) {
                        echo '<span style="color:#ff0000">Ответ '.($i + 1).':</span><br><input type="text" name="answer['.$answer[$i]['id'].']" maxlength="50" value="'.$answer[$i]['answer'].'"><br>';
                    } else {
                        echo 'Ответ '.($i + 1).':<br><input type="text" name="newanswer[]" maxlength="50"><br>';
                    }
                }

                echo '<input type="submit" value="Изменить"></form></div><br>';

                echo 'Поля отмеченные красным цветом обязательны для заполнения!<br><br>';
            } else {
                showError('Ошибка! Данного голосования не существует!');
            }

            echo '<i class="fa fa-arrow-circle-left"></i> <a href="/admin/votes">Вернуться</a><br>';
        break;

        ############################################################################################
        ##                                   Редактирование                                       ##
        ############################################################################################
        case 'change':

            $uid = check($_GET['uid']);
            $title = check($_POST['title']);
            $answer = check($_POST['answer']);

            if ($uid == $_SESSION['token']) {
                if (utfStrlen($title) >= 3 && utfStrlen($title) <= 100) {
                    $queryvote = DB::run() -> querySingle("SELECT `id` FROM `vote` WHERE `id`=? LIMIT 1;", [$id]);
                    if (!empty($queryvote)) {
                        if (!in_array('', $answer)) {
                            DB::update("UPDATE `vote` SET `title`=? WHERE `id`=?;", [$title, $id]);

                            $dbr = DB::run() -> prepare("UPDATE `voteanswer` SET `answer`=? WHERE `id`=?;");
                            foreach ($answer as $key => $data) {
                                $dbr -> execute($data, $key);
                            }

                            if (isset($_POST['newanswer'])) {
                                $newanswer = check($_POST['newanswer']);
                                $newanswer = array_diff($newanswer, ['']);
                                if (count($newanswer) > 0) {
                                    $dbr = DB::run() -> prepare("INSERT INTO `voteanswer` (`vote_id`, `answer`) VALUES (?, ?);");
                                    foreach ($newanswer as $data) {
                                        $dbr -> execute($id, $data);
                                    }
                                }
                            }

                            setFlash('success', 'Голосование успешно изменено!');
                            redirect("/admin/votes");
                        } else {
                            showError('Ошибка! Не заполнены все обязательные поля с ответами!');
                        }
                    } else {
                        showError('Ошибка! Данного голосования не существует!');
                    }
                } else {
                    showError('Ошибка! Слишком длинный или короткий вопрос (от 3 до 100 символов)!');
                }
            } else {
                showError('Ошибка! Неверный идентификатор сессии, повторите действие!');
            }

            echo '<i class="fa fa-arrow-circle-left"></i> <a href="/admin/votes?act=edit&amp;id='.$id.'">Вернуться</a><br>';
            echo '<i class="fa fa-chart-bar"></i> <a href="/admin/votes">К голосованиям</a><br>';
        break;

        ############################################################################################
        ##                                      Закрытие                                          ##
        ############################################################################################
        case 'action':

            $uid = check($_GET['uid']);
            $do = check($_GET['do']);

            if ($uid == $_SESSION['token']) {
                if ($do == 'close' || $do == 'open') {
                    $queryvote = DB::run() -> querySingle("SELECT `id` FROM `vote` WHERE `id`=? LIMIT 1;", [$id]);
                    if (!empty($queryvote)) {
                        if ($do == 'close') {
                            DB::update("UPDATE `vote` SET `closed`=? WHERE `id`=?;", [1, $id]);
                            DB::delete("DELETE FROM `votepoll` WHERE `vote_id`=?;", [$id]);
                            setFlash('success', 'Голосование успешно закрыто!');
                            redirect("/admin/votes");
                        }

                        if ($do == 'open') {
                            DB::update("UPDATE `vote` SET `closed`=? WHERE `id`=?;", [0, $id]);
                            setFlash('success', 'Голосование успешно открыто!');
                            redirect("/admin/votes?act=history");
                        }
                    } else {
                        showError('Ошибка! Данного голосования не существует!');
                    }
                } else {
                    showError('Ошибка! Не выбрано действие для голосования!');
                }
            } else {
                showError('Ошибка! Неверный идентификатор сессии, повторите действие!');
            }

            echo '<i class="fa fa-arrow-circle-left"></i> <a href="/admin/votes">Вернуться</a><br>';
        break;

        ############################################################################################
        ##                                      Удаление                                          ##
        ############################################################################################
        case 'del':

            $uid = check($_GET['uid']);

            if ($uid == $_SESSION['token']) {
                if (isAdmin([101])) {
                    $queryvote = DB::run() -> querySingle("SELECT `id` FROM `vote` WHERE `id`=? LIMIT 1;", [$id]);
                    if (!empty($queryvote)) {
                        DB::delete("DELETE FROM `vote` WHERE `id`=?;", [$id]);
                        DB::delete("DELETE FROM `voteanswer` WHERE `vote_id`=?;", [$id]);
                        DB::delete("DELETE FROM `votepoll` WHERE `vote_id`=?;", [$id]);

                        setFlash('success', 'Голосование успешно удалено!');
                        redirect("/admin/votes");
                    } else {
                        showError('Ошибка! Данного голосования не существует!');
                    }
                } else {
                    showError('Ошибка! Удалять голосования могут только суперадмины!');
                }
            } else {
                showError('Ошибка! Неверный идентификатор сессии, повторите действие!');
            }

            echo '<i class="fa fa-arrow-circle-left"></i> <a href="/admin/votes">Вернуться</a><br>';
        break;

        ############################################################################################
        ##                                    Пересчет счетчиков                                  ##
        ############################################################################################
        case 'rest':
            $uid = check($_GET['uid']);
            if ($uid == $_SESSION['token']) {
                if (isAdmin([101])) {
                    DB::update("UPDATE `vote` SET `count`=(SELECT SUM(`result`) FROM `voteanswer` WHERE `vote`.id=`voteanswer`.`vote_id`) WHERE `closed`=?;", [0]);

                    setFlash('success', 'Все данные успешно пересчитаны!');
                    redirect("/admin/votes");
                } else {
                    showError('Ошибка! Пересчитывать голосования могут только суперадмины!');
                }
            } else {
                showError('Ошибка! Неверный идентификатор сессии, повторите действие!');
            }

            echo '<i class="fa fa-arrow-circle-left"></i> <a href="/admin/votes">Вернуться</a><br>';
        break;

        ############################################################################################
        ##                                          История                                      ##
        ############################################################################################
        case 'history':

            $total = DB::run() -> querySingle("SELECT count(*) FROM `vote` WHERE `closed`=? ORDER BY `time`;", [1]);
            $page = paginate(setting('allvotes'), $total);

            if ($total > 0) {

                $queryvote = DB::select("SELECT * FROM `vote` WHERE `closed`=? ORDER BY `time` DESC LIMIT ".$page['offset'].", ".setting('allvotes').";", [1]);

                while ($data = $queryvote -> fetch()) {
                    echo '<div class="b">';
                    echo '<i class="fa fa-briefcase"></i> <b><a href="/votes/history?act=result&amp;id='.$data['id'].'&amp;page='.$page['current'].'">'.$data['title'].'</a></b><br>';

                    echo '<a href="/admin/votes?act=action&amp;do=open&amp;id='.$data['id'].'&amp;uid='.$_SESSION['token'].'">Открыть</a>';

                    if (isAdmin([101])) {
                        echo ' / <a href="/admin/votes?act=del&amp;id='.$data['id'].'&amp;uid='.$_SESSION['token'].'" onclick="return confirm(\'Вы подтверждаете удаление голосования?\')">Удалить</a>';
                    }

                    echo '</div>';
                    echo '<div>Создано: '.dateFixed($data['time']).'<br>';
                    echo 'Всего голосов: '.$data['count'].'</div>';
                }

                pagination($page);
            } else {
                showError('Голосований в архиве еще нет!');
            }

            echo '<i class="fa fa-chart-bar"></i> <a href="/admin/votes">Список голосований</a><br>';
        break;

    endswitch;

    echo '<i class="fa fa-wrench"></i> <a href="/admin">В админку</a><br>';

} else {
    redirect("/");
}

view(setting('themes').'/foot');
