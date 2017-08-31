<?php
view(setting('themes').'/index');

$act = (isset($_GET['act'])) ? check($_GET['act']) : 'index';

if (isAdmin([101, 102, 103])) {
    //show_title('Денежные операции');

    switch ($action):
    ############################################################################################
    ##                                    Главная страница                                    ##
    ############################################################################################
        case 'index':

            $total = DB::run() -> querySingle("SELECT COUNT(*) FROM `transfers`;");
            $page = paginate(setting('listtransfers'), $total);

            if ($total > 0) {

                $querytrans = DB::run() -> query("SELECT * FROM `transfers` ORDER BY `time` DESC LIMIT ".$page['offset'].", ".setting('listtransfers').";");

                while ($data = $querytrans -> fetch()) {
                    echo '<div class="b">';
                    echo '<div class="img">'.userAvatar($data['user']).'</div>';
                    echo '<b>'.profile($data['user']).'</b> '.userOnline($data['user']).' ';

                    echo '<small>('.dateFixed($data['time']).')</small><br>';

                    echo '<a href="/admin/transfers?act=view&amp;uz='.$data['user'].'">Все переводы</a></div>';

                    echo '<div>';
                    echo 'Кому: '.profile($data['login']).'<br>';
                    echo 'Сумма: '.moneys($data['summ']).'<br>';
                    echo 'Комментарий: '.$data['text'].'<br>';
                    echo '</div>';
                }

                pagination($page);

                echo '<div class="form">';
                echo '<b>Поиск по пользователю:</b><br>';
                echo '<form action="/admin/transfers?act=view" method="get">';
                echo '<input type="hidden" name="act" value="view">';
                echo '<input type="text" name="uz">';
                echo '<input type="submit" value="Искать"></form></div><br>';

                echo 'Всего операций: <b>'.$total.'</b><br><br>';

            } else {
                showError('Истории операций еще нет!');
            }
        break;

        ############################################################################################
        ##                                Просмотр по пользователям                               ##
        ############################################################################################
        case 'view':

            $uz = (isset($_GET['uz'])) ? check($_GET['uz']) : '';

            if (user($uz)) {

                $total = DB::run() -> querySingle("SELECT COUNT(*) FROM `transfers` WHERE `user`=?;", [$uz]);
                $page = paginate(setting('listtransfers'), $total);

                if ($total > 0) {

                    $queryhist = DB::run() -> query("SELECT * FROM `transfers` WHERE `user`=? ORDER BY `time` DESC LIMIT ".$page['offset'].", ".setting('listtransfers').";", [$uz]);

                    while ($data = $queryhist -> fetch()) {
                        echo '<div class="b">';
                        echo '<div class="img">'.userAvatar($data['user']).'</div>';
                        echo '<b>'.profile($data['user']).'</b> '.userOnline($data['user']).' ';

                        echo '<small>('.dateFixed($data['time']).')</small>';
                        echo '</div>';

                        echo '<div>';
                        echo 'Кому: '.profile($data['login']).'<br>';
                        echo 'Сумма: '.moneys($data['summ']).'<br>';
                        echo 'Комментарий: '.$data['text'].'<br>';
                        echo '</div>';
                    }

                    pagination($page);

                    echo 'Всего операций: <b>'.$total.'</b><br><br>';

                } else {
                    showError('Истории операций еще нет!');
                }
            } else {
                showError('Ошибка! Данный пользователь не найден!');
            }

            echo '<i class="fa fa-arrow-circle-left"></i> <a href="/admin/transfers">Вернуться</a><br>';
        break;

    endswitch;

    echo '<i class="fa fa-wrench"></i> <a href="/admin">В админку</a><br>';

} else {
    redirect("/");
}

view(setting('themes').'/foot');
