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
$page = abs(intval(Request::input('page', 1)));

switch ($action):
############################################################################################
##                                    Главная страница                                    ##
############################################################################################
    case 'index':

        //show_title('История голосований');

        $total = DB::run() -> querySingle("SELECT count(*) FROM `vote` WHERE `closed`=? ORDER BY `time`;", [1]);
        $page = App::paginate(Setting::get('allvotes'), $total);

        if ($total > 0) {

            $queryvote = DB::run() -> query("SELECT * FROM `vote` WHERE `closed`=? ORDER BY `time` DESC LIMIT ".$page['offset'].", ".Setting::get('allvotes').";", [1]);

            while ($data = $queryvote -> fetch()) {
                echo '<div class="b">';
                echo '<i class="fa fa-briefcase"></i> <b><a href="/votes/history?act=result&amp;id='.$data['id'].'&amp;page='.$page['current'].'">'.$data['title'].'</a></b></div>';
                echo '<div>Создано: '.date_fixed($data['time']).'<br>';
                echo 'Всего голосов: '.$data['count'].'</div>';
            }

            App::pagination($page);
        } else {
            App::showError('Голосований в архиве еще нет!');
        }
    break;

    ############################################################################################
    ##                                      Голосование                                       ##
    ############################################################################################
    case 'result':
        //show_title('Результаты голосований');

        $votes = DB::run() -> queryFetch("SELECT * FROM `vote` WHERE `id`=? LIMIT 1;", [$id]);

        if (!empty($votes)) {
            if (!empty($votes['closed'])) {
                //Setting::get('newtitle') = $votes['title'];

                echo '<i class="fa fa-briefcase"></i> <b>'.$votes['title'].'</b> (Голосов: '.$votes['count'].')<br><br>';

                $queryanswer = DB::run() -> query("SELECT `answer`, `result` FROM `voteanswer` WHERE `vote_id`=? ORDER BY `result` DESC;", [$id]);
                $answer = $queryanswer -> fetchAssoc();

                $total = count($answer);

                if ($total > 0) {
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

                        echo '<b>'.$key.'</b> (Голосов: '.$data.')<br>';
                        progress_bar($maxproc, $proc.'%').'<br><br>';
                    }

                    echo 'Вариантов: <b>'.$total.'</b><br><br>';
                } else {
                    App::showError('Ошибка! Для данного голосования не созданы варианты ответов!');
                }
            } else {
                App::showError('Ошибка! Данного опроса не существует в истории!');
            }
        } else {
            App::showError('Ошибка! Данного голосования не существует!');
        }

        echo '<i class="fa fa-arrow-circle-left"></i> <a href="/votes/history?page='.$page.'">Вернуться</a><br>';
    break;

endswitch;

echo '<i class="fa fa-bar-chart"></i> <a href="/votes">Список голосований</a><br>';

App::view(Setting::get('themes').'/foot');
