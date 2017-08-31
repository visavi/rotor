<?php
view(setting('themes').'/index');

//show_title('Администрация сайта');
############################################################################################
##                                     Вывод администрации                                ##
############################################################################################
$queryadmin = DB::run() -> query("SELECT `login`, `level` FROM `users` WHERE `level`>=? AND `level`<=?;", [101, 105]);
$arradmin = $queryadmin -> fetchAll();
$total = count($arradmin);

if ($total > 0) {
    foreach($arradmin as $value) {
        echo user_gender($value['login']).' <b>'.profile($value['login']).'</b>  ('.user_status($value['level']).') '.user_online($value['login']).'<br>';
    }

    echo '<br>Всего в администрации: <b>'.$total.'</b><br><br>';
    ############################################################################################
    ##                                     Быстрая почта                                      ##
    ############################################################################################
    if (is_user()) {
        echo '<big><b>Быстрая почта</b></big><br><br>';

        echo '<div class="form">';
        echo '<form method="post" action="/private?act=send&amp;uid='.$_SESSION['token'].'">';

        echo 'Выберите адресат:<br><select name="uz">';

        foreach($arradmin as $value) {
            echo '<option value="'.$value['login'].'"> '.$value['login'].' </option>';
        }
        echo '</select><br>';
        echo 'Сообщение:<br>';
        echo '<textarea cols="25" rows="5" name="msg"></textarea><br>';

        if (user('point') < setting('privatprotect')) {
            echo 'Проверочный код:<br> ';
            echo '<img src="/captcha" alt=""><br>';
            echo '<input name="provkod" size="6" maxlength="6"><br>';
        }

        echo '<input value="Отправить" type="submit"></form></div><br>';
    }
} else {
    showError('Администрации еще нет!');
}

view(setting('themes').'/foot');
