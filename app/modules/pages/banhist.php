<?php
App::view($config['themes'].'/index');

if (empty($_GET['uz'])) {
    $uz = check($log);
} else {
    $uz = check(strval($_GET['uz']));
}
if (isset($_GET['start'])) {
    $start = abs(intval($_GET['start']));
} else {
    $start = 0;
}

if (is_user()) {
    show_title('История банов '.nickname($uz));

    $total = DB::run() -> querySingle("SELECT COUNT(*) FROM `banhist` WHERE `user`=?;", [$uz]);

    if ($total > 0) {
        if ($start >= $total) {
            $start = 0;
        }

        $queryhist = DB::run() -> query("SELECT * FROM `banhist` WHERE `user`=time` DESC LIMIT ".$start.", ".$config['listbanhist'].";", [$uz]);

        while ($data = $queryhist -> fetch()) {
            echo '<div class="b">';
            echo '<i class="fa fa-history"></i> ';
            echo '<b>'.profile($data['user']).'</b> ('.date_fixed($data['time']).')</div>';

            echo '<div>';
            if (!empty($data['type'])) {
                echo 'Причина: '.bb_code($data['reason']).'<br />';
                echo 'Срок: '.formattime($data['term']).'<br />';
            }

            switch ($data['type']) {
                case '1': $stat = '<span style="color:#ff0000">Забанил</span>:';
                    break;
                case '2': $stat = '<span style="color:#ffa500">Изменил</span>:';
                    break;
                default: $stat = '<span style="color:#00cc00">Разбанил</span>:';
            }

            echo $stat.' '.profile($data['send']).'<br />';

            echo '</div>';
        }

        page_strnavigation('/banhist?uz='.$uz.'&amp;', $config['listbanhist'], $start, $total);

        echo 'Всего действий: <b>'.$total.'</b><br /><br />';
    } else {
        show_error('В истории еще ничего нет!');
    }
} else {
    show_login('Вы не авторизованы, чтобы просматривать историю, необходимо');
}

echo '<i class="fa fa-arrow-circle-left"></i> <a href="/user/'.$uz.'">В анкету</a><br />';

App::view($config['themes'].'/foot');
