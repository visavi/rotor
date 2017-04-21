<?php
App::view(App::setting('themes').'/index');

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

switch ($act):
############################################################################################
##                                    Главная страница                                    ##
############################################################################################
    case 'index':
        //show_title('Голосования');

        $queryvote = DB::run() -> query("SELECT * FROM `vote` WHERE `closed`=? ORDER BY `time` DESC;", [0]);
        $votes = $queryvote -> fetchAll();

        if (count($votes) > 0) {
            foreach($votes as $valvote) {
                echo '<div class="b">';
                echo '<i class="fa fa-bar-chart"></i> <b><a href="/votes?act=poll&amp;id='.$valvote['id'].'">'.$valvote['title'].'</a></b></div>';
                echo '<div>Создано: '.date_fixed($valvote['time']).'<br />';
                echo 'Всего голосов: '.$valvote['count'].'</div>';
            }
            echo '<br />';
        } else {
            show_error('Открытых голосований еще нет!');
        }
    break;

    ############################################################################################
    ##                                      Голосование                                       ##
    ############################################################################################
    case 'poll':

        $votes = DB::run() -> queryFetch("SELECT * FROM `vote` WHERE `id`=? LIMIT 1;", [$id]);

        if (!empty($votes)) {
            if (empty($votes['closed'])) {

                //show_title($votes['title']);
                //App::setting('newtitle') = $votes['title'];

                $queryanswer = DB::run() -> query("SELECT * FROM `voteanswer` WHERE `vote_id`=? ORDER BY `id`;", [$id]);
                $answer = $queryanswer -> fetchAll();

                if ($answer) {
                    $polls = DB::run() -> querySingle("SELECT `id` FROM `votepoll` WHERE `vote_id`=? AND `user`=? LIMIT 1;", [$id, $log]);

                    if ((is_user() && empty($polls)) && empty($_GET['result'])) {

                        echo '<form action="/votes?act=vote&amp;id='.$id.'&amp;uid='.$_SESSION['token'].'" method="post">';

                        foreach($answer as $data) {
                            echo '<input name="poll" type="radio" value="'.$data['id'].'" /> '.$data['answer'].'<br />';
                        }

                        echo '<br /><input type="submit" value="Голосовать" /></form><br />';

                        echo 'Проголосовало: <b>'.$votes['count'].'</b><br /><br />';
                        echo '<i class="fa fa-history"></i> <a href="/votes?act=poll&amp;id='.$id.'&amp;result=show">Результаты</a><br />';

                    } else {

                        $queryanswer = DB::run() -> query("SELECT `answer`, `result` FROM `voteanswer` WHERE `vote_id`=? ORDER BY `result` DESC;", [$id]);
                        $answer = $queryanswer -> fetchAssoc();

                        $sum = $votes['count'];
                        $max = max($answer);

                        if (empty($sum)) {
                            $sum = 1;
                        }
                        if (empty($max)) {
                            $max = 1;
                        }

                        foreach($answer as $key => $data) {
                            $proc = round(($data * 100) / $sum, 1);
                            $maxproc = round(($data * 100) / $max);

                            echo '<b>'.$key.'</b> (Голосов: '.$data.')<br />';
                            App::progressBar($maxproc, $proc.'%');
                        }

                        echo 'Проголосовало: <b>'.$votes['count'].'</b><br /><br />';

                        if (!empty($_GET['result'])) {
                            echo '<i class="fa fa-bar-chart"></i> <a href="/votes?act=poll&amp;id='.$id.'">К вариантам</a><br />';
                        }
                        echo '<i class="fa fa-users"></i> <a href="/votes?act=voters&amp;id='.$id.'">Проголосовавшие</a><br />';
                    }

                } else {
                    show_error('Ошибка! Для данного голосования не созданы варианты ответов!');
                }
            } else {
                show_error('Ошибка! Данный опрос закрыт для голосования!');
            }
        } else {
            show_error('Ошибка! Данного голосования не существует!');
        }

        echo '<i class="fa fa-arrow-circle-up"></i> <a href="/votes">К голосованиям</a><br />';
    break;

    ############################################################################################
    ##                                      Голосование                                       ##
    ############################################################################################
    case 'vote':
        //show_title('Голосование');

        $uid = check($_GET['uid']);
        if (isset($_POST['poll'])) {
            $poll = abs(intval($_POST['poll']));
        } else {
            $poll = 0;
        }

        if (is_user()) {
            if ($uid == $_SESSION['token']) {
                if (!empty($poll)) {
                    $votes = DB::run() -> queryFetch("SELECT * FROM `vote` WHERE `id`=? LIMIT 1;", [$id]);

                    if (!empty($votes)) {
                        if (empty($votes['closed'])) {
                            $queryanswer = DB::run() -> querySingle("SELECT `vote_id` FROM `voteanswer` WHERE `id`=? AND `vote_id`=?  LIMIT 1;", [$poll, $id]);
                            if (!empty($queryanswer)) {

                                $polls = DB::run() -> querySingle("SELECT `id` FROM `votepoll` WHERE `vote_id`=? AND `user`=? LIMIT 1;", [$id, $log]);
                                if (empty($polls)) {

                                    DB::run() -> query("UPDATE `vote` SET `count`=`count`+1 WHERE `id`=?;", [$id]);
                                    DB::run() -> query("UPDATE `voteanswer` SET `result`=`result`+1 WHERE `id`=?;", [$poll]);
                                    DB::run() -> query("INSERT INTO `votepoll` (`vote_id`, `user`, `time`) VALUES (?, ?, ?);", [$id, $log, SITETIME]);

                                    notice('Ваш голос успешно принят!');
                                    redirect("/votes?act=poll&id=$id");

                                } else {
                                    show_error('Ошибка! Вы уже проголосовали в этом опросе!');
                                }
                            } else {
                                show_error('Ошибка! Для данного голосования не созданы варианты ответов!');
                            }
                        } else {
                            show_error('Ошибка! Данный опрос закрыт для голосования!');
                        }
                    } else {
                        show_error('Ошибка! Данного голосования не существует!');
                    }
                } else {
                    show_error('Ошибка! Вы не выбрали вариант ответа!');
                }
            } else {
                show_error('Ошибка! Неверный идентификатор сессии, повторите действие!');
            }
        } else {
            show_login('Вы не авторизованы, чтобы участвовать в голосованиях, необходимо');
        }

        echo '<i class="fa fa-arrow-circle-left"></i> <a href="/votes?act=poll&amp;id='.$id.'">Вернуться</a><br />';
        echo '<i class="fa fa-arrow-circle-up"></i> <a href="/votes">К голосованиям</a><br />';
    break;

    ############################################################################################
    ##                                      Голосование                                       ##
    ############################################################################################
    case 'voters':
        //show_title('Последние проголосовавшие');

        $votes = DB::run() -> queryFetch("SELECT * FROM `vote` WHERE `id`=? LIMIT 1;", [$id]);
        if (!empty($votes)) {

            //App::setting('newtitle') = $votes['title'];

            echo '<i class="fa fa-bar-chart"></i> <b>'.$votes['title'].'</b> (Голосов: '.$votes['count'].')<br /><br />';

            $querypoll = DB::run() -> query("SELECT `user`, `time` FROM `votepoll` WHERE `vote_id`=? ORDER BY `time` DESC LIMIT 20;", [$id]);
            $polls = $querypoll -> fetchAll();

            foreach($polls as $poll){
                echo user_gender($poll['user']).profile($poll['user']).' ('.date_fixed($poll['time']).')<br />';
            }

            echo '<br />';

        } else {
            show_error('Ошибка! Данного голосования не существует!');
        }

        echo '<i class="fa fa-arrow-circle-left"></i> <a href="/votes?act=poll&amp;id='.$id.'">Вернуться</a><br />';
        echo '<i class="fa fa-arrow-circle-up"></i> <a href="/votes">К голосованиям</a><br />';
    break;

endswitch;

echo '<i class="fa fa-briefcase"></i> <a href="/votes/history">Архив голосований</a><br />';

App::view(App::setting('themes').'/foot');
