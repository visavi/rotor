<?php
App::view($config['themes'].'/index');

if (isset($_GET['act'])) {
    $act = check($_GET['act']);
} else {
    $act = 'index';
}

if (isset($_GET['page'])) {
    $page = check($_GET['page']);
} else {
    $page = 'mail';
}

if (isset($_GET['start'])) {
    $start = abs(intval($_GET['start']));
} else {
    $start = 0;
}

if (is_admin(array(101, 102))) {
    show_title('Черный список');

    switch ($page) {
        case 'login':
            $type = 2;
            $placeholder = '';
            break;
        case 'domain':
            $type = 3;
            $placeholder = 'http://';
            break;
        default:
            $type = 1;
            $placeholder = '';
    }

/* 	$links = array (
        array('page' => 'mail', 'name' => 'E-mail'),
        array('page' => 'login', 'name' => 'Логины'),
        array('page' => 'domain', 'name' => 'Домены')
    );

    echo 'Запрещенные: ';

    foreach ($links as $key => $link){
        $active = ($page == $link['page']) ? ' style="font-weight: bold;"' : '';
        $separator = ($key==0) ?  '' : ' / ';

        echo $separator.'<a href="/admin/blacklist?page='.$link['page'].'"'.$active.'>'.$link['name'].'</a>';
    }

    echo '<hr />'; */

    echo 'Запрещенные: <a href="/admin/blacklist"'.(($type == 1) ? ' style="font-weight: bold;"' : '').'>E-mail</a> / <a href="/admin/blacklist?page=login"'.(($type == 2) ? ' style="font-weight: bold;"' : '').'>Логины</a> / <a href="/admin/blacklist?page=domain"'.(($type == 3) ? ' style="font-weight: bold;"' : '').'>Домены</a><hr />';

    switch ($act):
    ############################################################################################
    ##                                    Главная страница                                    ##
    ############################################################################################
        case 'index':

            $total = DB::run() -> querySingle("SELECT count(*) FROM `blacklist` WHERE `black_type`=?;", array($type));

            if ($total > 0) {
                if ($start >= $total) {
                    $start = 0;
                }

                $queryblack = DB::run() -> query("SELECT * FROM `blacklist` WHERE `black_type`=? ORDER BY `black_time` DESC LIMIT ".$start.", ".$config['blacklist'].";", array($type));

                echo '<form action="/admin/blacklist?act=del&amp;page='.$page.'&amp;start='.$start.'&amp;uid='.$_SESSION['token'].'" method="post">';

                while ($data = $queryblack -> fetch()) {
                    echo '<div class="b">';
                    echo '<input type="checkbox" name="del[]" value="'.$data['black_id'].'" /> ';

                    echo '<i class="fa fa-pencil"></i> <b>'.$data['black_value'].'</b></div>';
                    echo '<div>Добавлено: '.profile($data['black_user']).'<br />';
                    echo 'Время: '.date_fixed($data['black_time']).'</div>';
                }
                echo '<br /><input type="submit" value="Удалить выбранное" /></form>';

                page_strnavigation('/admin/blacklist?page='.$page.'&amp;', $config['blacklist'], $start, $total);
            } else {
                show_error('Cписок еще пуст!');
            }

            echo '<div class="form">';
            echo '<form action="/admin/blacklist?act=add&amp;page='.$page.'&amp;uid='.$_SESSION['token'].'" method="post">';
            echo '<b>Запись:</b><br />';
            echo '<input name="value" type="text" maxlength="100" value="'.$placeholder.'" />';
            echo '<input type="submit" value="Добавить" /></form></div><br />';

            echo 'Всего в списке: <b>'.$total.'</b><br /><br />';
        break;

        ############################################################################################
        ##                                 Добавление записи                                      ##
        ############################################################################################
        case 'add':

            $uid = check($_GET['uid']);
            $value = check(utf_lower($_POST['value']));

            if ($uid == $_SESSION['token']) {
                if (!empty($value) && utf_strlen($value) <= 100) {
                    if ($page != 'mail' || preg_match('#^([a-z0-9_\-\.])+\@([a-z0-9_\-\.])+(\.([a-z0-9])+)+$#', $value)) {
                        if ($page != 'login' || preg_match('|^[a-z0-9\-]+$|', $value)) {
                            if ($page != 'domain' || preg_match('#^http://([а-яa-z0-9_\-\.])+(\.([а-яa-z0-9\/])+)+$#u', $value)) {

                                $value = str_replace('http://', '', $value);

                                $black = DB::run() -> querySingle("SELECT `black_id` FROM `blacklist` WHERE `black_type`=? AND `black_value`=? LIMIT 1;", array($type, $value));
                                if (empty($black)) {
                                    DB::run() -> query("INSERT INTO `blacklist` (`black_type`, `black_value`, `black_user`, `black_time`) VALUES (?, ?, ?, ?);", array($type, $value, $log, SITETIME));

                                    notice('Запись успешно добавлена в черный список!');
                                    redirect("/admin/blacklist?page=$page&start=$start");

                                } else {
                                    show_error('Ошибка! Данная запись уже имеется в списках!');
                                }
                            } else {
                                show_error('Ошибка! Недопустимый адрес сайта! (http://sitename.domen)!');
                            }
                        } else {
                            show_error('Ошибка! Недопустимые символы в логине. Разрешены знаки латинского алфавита, цифры и дефис!');
                        }
                    } else {
                        show_error('Ошибка! Недопустимый адрес e-mail, необходим формат name@site.domen!');
                    }
                } else {
                    show_error('Ошибка! Вы не ввели запись или она слишком длинная!');
                }
            } else {
                show_error('Ошибка! Неверный идентификатор сессии, повторите действие!');
            }

            echo '<i class="fa fa-arrow-circle-left"></i> <a href="/admin/blacklist?page='.$page.'&amp;start='.$start.'">Вернуться</a><br />';
        break;


        ############################################################################################
        ##                                   Удаление записей                                     ##
        ############################################################################################
        case 'del':

            $uid = check($_GET['uid']);
            if (isset($_POST['del'])) {
                $del = intar($_POST['del']);
            } else {
                $del = 0;
            }

            if ($uid == $_SESSION['token']) {
                if (!empty($del)) {
                    $del = implode(',', $del);

                    DB::run() -> query("DELETE FROM `blacklist` WHERE `black_type`=? AND `black_id` IN (".$del.");", array($type));

                    notice('Выбранные записи успешно удалены!');
                    redirect("/admin/blacklist?page=$page&start=$start");
                } else {
                    show_error('Ошибка! Отсутствуют выбранные записи для удаления!');
                }
            } else {
                show_error('Ошибка! Неверный идентификатор сессии, повторите действие!');
            }

            echo '<i class="fa fa-arrow-circle-left"></i> <a href="/admin/blacklist?page='.$page.'&amp;start='.$start.'">Вернуться</a><br />';
        break;

    endswitch;

    echo '<i class="fa fa-wrench"></i> <a href="/admin">В админку</a><br />';

} else {
    redirect('/');
}

App::view($config['themes'].'/foot');
