<?php
App::view(Setting::get('themes').'/index');

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

switch ($action):
############################################################################################
##                                    Главная страница                                    ##
############################################################################################


    ############################################################################################
    ##                                      Голосование                                       ##
    ############################################################################################
    case 'poll':


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

                                $polls = DB::run() -> querySingle("SELECT `id` FROM `votepoll` WHERE `vote_id`=? AND `user`=? LIMIT 1;", [$id, App::getUsername()]);
                                if (empty($polls)) {

                                    DB::run() -> query("UPDATE `vote` SET `count`=`count`+1 WHERE `id`=?;", [$id]);
                                    DB::run() -> query("UPDATE `voteanswer` SET `result`=`result`+1 WHERE `id`=?;", [$poll]);
                                    DB::run() -> query("INSERT INTO `votepoll` (`vote_id`, `user`, `time`) VALUES (?, ?, ?);", [$id, App::getUsername(), SITETIME]);

                                    App::setFlash('success', 'Ваш голос успешно принят!');
                                    App::redirect("/votes?act=poll&id=$id");

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

        echo '<i class="fa fa-arrow-circle-left"></i> <a href="/votes?act=poll&amp;id='.$id.'">Вернуться</a><br>';
        echo '<i class="fa fa-arrow-circle-up"></i> <a href="/votes">К голосованиям</a><br>';
    break;

    ############################################################################################
    ##                                      Голосование                                       ##
    ############################################################################################
    case 'voters':
        //show_title('Последние проголосовавшие');

        $votes = DB::run() -> queryFetch("SELECT * FROM `vote` WHERE `id`=? LIMIT 1;", [$id]);
        if (!empty($votes)) {

            //Setting::get('newtitle') = $votes['title'];

            echo '<i class="fa fa-bar-chart"></i> <b>'.$votes['title'].'</b> (Голосов: '.$votes['count'].')<br><br>';

            $querypoll = DB::run() -> query("SELECT `user`, `time` FROM `votepoll` WHERE `vote_id`=? ORDER BY `time` DESC LIMIT 20;", [$id]);
            $polls = $querypoll -> fetchAll();

            foreach($polls as $poll){
                echo user_gender($poll['user']).profile($poll['user']).' ('.date_fixed($poll['time']).')<br>';
            }

            echo '<br>';

        } else {
            show_error('Ошибка! Данного голосования не существует!');
        }

        echo '<i class="fa fa-arrow-circle-left"></i> <a href="/votes?act=poll&amp;id='.$id.'">Вернуться</a><br>';
        echo '<i class="fa fa-arrow-circle-up"></i> <a href="/votes">К голосованиям</a><br>';
    break;

endswitch;

echo '<i class="fa fa-briefcase"></i> <a href="/votes/history">Архив голосований</a><br>';

App::view(Setting::get('themes').'/foot');
