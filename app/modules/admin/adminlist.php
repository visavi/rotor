<?php
App::view(Setting::get('themes').'/index');

if (is_admin([101, 102, 103])) {
    //show_title('Администрация сайта');
    ############################################################################################
    ##                                     Вывод администрации                                ##
    ############################################################################################
    $queryadmin = DB::run() -> query("SELECT login, level FROM users WHERE level>=? AND level<=?;", [101, 105]);
    $arradmin = $queryadmin -> fetchAll();
    $total = count($arradmin);

    if ($total > 0) {
        foreach($arradmin as $value) {
            echo '<i class="fa fa-user-circle-o"></i> <b>'.profile($value['login']).'</b>  ('.user_status($value['level']).') '.user_online($value['login']).'<br>';

            if (is_admin([101])) {
                echo '<i class="fa fa-pencil"></i> <a href="/admin/users?act=edit&amp;uz='.$value['login'].'">Изменить</a><br>';
            }
        }
        echo '<br>Всего в администрации: <b>'.$total.'</b><br><br>';

    } else {
        show_error('Администрации еще нет!');
    }

    echo '<i class="fa fa-wrench"></i> <a href="/admin">В админку</a><br>';

} else {
    App::redirect("/");
}

App::view(Setting::get('themes').'/foot');

