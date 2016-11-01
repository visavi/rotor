<?php
App::view($config['themes'].'/index');

if (is_admin(array(101, 102, 103))) {
    show_title('Администрация сайта');
    ############################################################################################
    ##                                     Вывод администрации                                ##
    ############################################################################################
    $queryadmin = DB::run() -> query("SELECT users_login, users_level FROM users WHERE users_level>=? AND users_level<=?;", array(101, 105));
    $arradmin = $queryadmin -> fetchAll();
    $total = count($arradmin);

    if ($total > 0) {
        foreach($arradmin as $value) {
            echo '<i class="fa fa-user-circle-o"></i> <b>'.profile($value['users_login']).'</b>  ('.user_status($value['users_level']).') '.user_online($value['users_login']).'<br />';

            if (is_admin(array(101))) {
                echo '<i class="fa fa-pencil"></i> <a href="/admin/users?act=edit&amp;uz='.$value['users_login'].'">Изменить</a><br />';
            }
        }
        echo '<br />Всего в администрации: <b>'.$total.'</b><br /><br />';

    } else {
        show_error('Администрации еще нет!');
    }

    echo '<i class="fa fa-wrench"></i> <a href="/admin">В админку</a><br />';

} else {
	redirect("/");
}

App::view($config['themes'].'/foot');

