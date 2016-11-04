<?php
App::view($config['themes'].'/index');

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
        show_title('Голосования');

        $queryvote = DB::run() -> query("SELECT * FROM `vote` WHERE `vote_closed`=? ORDER BY `vote_time` DESC;", array(0));
        $votes = $queryvote -> fetchAll();

        if (count($votes) > 0) {
            foreach($votes as $valvote) {
                echo '<div class="b">';
                echo '<i class="fa fa-bar-chart"></i> <b><a href="/votes?act=poll&amp;id='.$valvote['vote_id'].'">'.$valvote['vote_title'].'</a></b></div>';
                echo '<div>Создано: '.date_fixed($valvote['vote_time']).'<br />';
                echo 'Всего голосов: '.$valvote['vote_count'].'</div>';
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
        show_title('site.png', 'Голосование');

        $votes = DB::run() -> queryFetch("SELECT * FROM `vote` WHERE `vote_id`=? LIMIT 1;", array($id));

        if (!empty($votes)) {
            if (empty($votes['vote_closed'])) {
                $config['newtitle'] = $votes['vote_title'];

                echo '<i class="fa fa-bar-chart"></i> <b>'.$votes['vote_title'].'</b> (Голосов: '.$votes['vote_count'].')<br /><br />';

                $queryanswer = DB::run() -> query("SELECT * FROM `voteanswer` WHERE `answer_vote_id`=? ORDER BY `answer_id`;", array($id));
                $answer = $queryanswer -> fetchAll();

                $total = count($answer);
                if ($total > 0) {
                    $polls = DB::run() -> querySingle("SELECT `poll_id` FROM `votepoll` WHERE `poll_vote_id`=? AND `poll_user`=? LIMIT 1;", array($id, $log));

                    if ((is_user() && empty($polls)) && empty($_GET['result'])) {


                        echo '<form action="/votes?act=vote&amp;id='.$id.'&amp;uid='.$_SESSION['token'].'" method="post">';

                        foreach($answer as $data) {
                            echo '<input name="poll" type="radio" value="'.$data['answer_id'].'" /> '.$data['answer_option'].'<br />';
                        }

                        echo '<br /><input type="submit" value="Голосовать" /></form><br />';

                        echo 'Всего вариантов: <b>'.$total.'</b><br /><br />';
                        echo '<i class="fa fa-history"></i> <a href="/votes?act=poll&amp;id='.$id.'&amp;result=show">Результаты</a><br />';

                    } else {

                        $queryanswer = DB::run() -> query("SELECT `answer_option`, `answer_result` FROM `voteanswer` WHERE `answer_vote_id`=? ORDER BY `answer_result` DESC;", array($id));
                        $answer = $queryanswer -> fetchAssoc();

                        $total = count($answer);

                        $sum = $votes['vote_count'];
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
                            progress_bar($maxproc, $proc.'%').'<br /><br />';
                        }

                        echo 'Вариантов: <b>'.$total.'</b><br /><br />';

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
        show_title('Голосование');

        $uid = check($_GET['uid']);
        if (isset($_POST['poll'])) {
            $poll = abs(intval($_POST['poll']));
        } else {
            $poll = 0;
        }

        if (is_user()) {
            if ($uid == $_SESSION['token']) {
                if (!empty($poll)) {
                    $votes = DB::run() -> queryFetch("SELECT * FROM `vote` WHERE `vote_id`=? LIMIT 1;", array($id));

                    if (!empty($votes)) {
                        if (empty($votes['vote_closed'])) {
                            $queryanswer = DB::run() -> querySingle("SELECT `answer_vote_id` FROM `voteanswer` WHERE `answer_id`=? AND `answer_vote_id`=?  LIMIT 1;", array($poll, $id));
                            if (!empty($queryanswer)) {

                                $polls = DB::run() -> querySingle("SELECT `poll_id` FROM `votepoll` WHERE `poll_vote_id`=? AND `poll_user`=? LIMIT 1;", array($id, $log));
                                if (empty($polls)) {

                                    DB::run() -> query("UPDATE `vote` SET `vote_count`=`vote_count`+1 WHERE `vote_id`=?;", array($id));
                                    DB::run() -> query("UPDATE `voteanswer` SET `answer_result`=`answer_result`+1 WHERE `answer_id`=?;", array($poll));
                                    DB::run() -> query("INSERT INTO `votepoll` (`poll_vote_id`, `poll_user`, `poll_time`) VALUES (?, ?, ?);", array($id, $log, SITETIME));

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
        show_title('Последние проголосовавшие');

        $votes = DB::run() -> queryFetch("SELECT * FROM `vote` WHERE `vote_id`=? LIMIT 1;", array($id));
        if (!empty($votes)) {

            $config['newtitle'] = $votes['vote_title'];

            echo '<i class="fa fa-bar-chart"></i> <b>'.$votes['vote_title'].'</b> (Голосов: '.$votes['vote_count'].')<br /><br />';

            $querypoll = DB::run() -> query("SELECT `poll_user`, `poll_time` FROM `votepoll` WHERE `poll_vote_id`=? ORDER BY `poll_time` DESC LIMIT 20;", array($id));
            $polls = $querypoll -> fetchAll();

            foreach($polls as $poll){
                echo user_gender($poll['poll_user']).profile($poll['poll_user']).' ('.date_fixed($poll['poll_time']).')<br />';
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

App::view($config['themes'].'/foot');
