<?php
view(setting('themes').'/index');

if (isAdmin([101, 102, 103])) {
    //show_title('Администрация сайта');
    ############################################################################################
    ##                                     Вывод администрации                                ##
    ############################################################################################
    $queryadmin = DB::select("SELECT login, level FROM users WHERE level>=? AND level<=?;", [101, 105]);
    $arradmin = $queryadmin -> fetchAll();
    $total = count($arradmin);

    if ($total > 0) {
        foreach($arradmin as $value) {
            echo '<i class="fa fa-user-circle"></i> <b>'.profile($value['login']).'</b>  ('.userLevel($value['level']).') '.userOnline($value['login']).'<br>';

            if (isAdmin([101])) {
                echo '<i class="fa fa-pencil-alt"></i> <a href="/admin/users?act=edit&amp;uz='.$value['login'].'">Изменить</a><br>';
            }
        }
        echo '<br>Всего в администрации: <b>'.$total.'</b><br><br>';

    } else {
        showError('Администрации еще нет!');
    }

    echo '<i class="fa fa-wrench"></i> <a href="/admin">В админку</a><br>';

} else {
    redirect("/");
}

view(setting('themes').'/foot');

