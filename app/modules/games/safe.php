<?php
App::view($config['themes'].'/index');

show_title('Взлом сейфа');

$act = (isset($_GET['act'])) ? check($_GET['act']) : 'index';

if (!$log){
    notice('Гостям сюда нельзя!');
    redirect('/');
}

switch ($act):

case 'index':
    echo '<img src="/images/safe/safe-closed.png" alt="сейф"/><br />';
    echo 'Ну что '.nickname($log).', взломаем?<br />';
    echo 'У тебя '.moneys($udata['users_money']).'<br />';

    $_SESSION['code'] = sprintf('%04d', mt_rand(0,9999));

    $split = str_split($_SESSION['code']);

    $_SESSION['try'] = 5;
    $_SESSION['s1'] = $split[0];
    $_SESSION['s2'] = $split[1];
    $_SESSION['s3'] = $split[2];
    $_SESSION['s4'] = $split[3];

    echo 'Всё готово для совершения взлома! Перейдите по ссылке Лoмaть ceйф!<br />';

    echo 'Попробуй вскрыть наш сейф.
    <br />В сейфе тебя ждёт: '.moneys($config['safesum']).' (плaтишь 1 paз зa 5 пoпытoк)<br />
    За попытку взлома ты заплатишь '.moneys($config['safeattempt']).'. Ну это чтобы купить себе необходимое для взлома оборудование.<br />
    У тебя будет только 5 попыток чтобы подобрать код из 4-х цифр.<br />
    Если тебя это устраивает, то ВПЕРЁД!<br />';

    if($udata['users_money']<$config['safeattempt']){
        echo 'У тебя не достаточно денег!';
    }else{
        echo '&#187; <a href="/games/safe?act=vzlom">Лoмaть ceйф</a><br /><br />';
    }
break;

case 'vzlom':

    if ($udata['users_money'] < $config['safeattempt']) {
            notice('У тебя нет таких денег!');
            redirect('/games/safe');
    }else{

        if (empty($_SESSION['go']) || !$_SESSION['try']){
            DB::run() -> query("UPDATE `users` SET `users_money`=`users_money`-? WHERE `users_login`=? LIMIT 1;", array($config['safeattempt'], $log));
            $_SESSION['go'] = 'ok';
        }

        echo nickname($log).', не торопись! Просто хорошо подумай. <br />';
        echo '<br /><img src="/images/safe/safe-closed.png" alt="сейф"/><br />';

        if (!$_SESSION['code'] || !$_SESSION['go']){
            notice('Нееее.... такое тут не канает!');
            redirect('/games/safe');
        } else {

            if ($_SESSION['try']==0) {
                echo '<img src="/images/safe/safe-closed.png" alt="сейф"/><br />';
                echo '<br />Попытки закончились. A взломать сейф так и не получилось...
                Возможно, в другой раз тебе повезёт больше...<br />';
                echo '<br />&raquo; <a href="/games/safe">Ещё разок</a>';
            } else {

                echo 'Попыток осталось: '.$_SESSION['try'].'<br />';
                echo 'Комбинация сейфа:<br />';
                echo '<font color="red">- - - -</font><br />';
                echo '<form action="/games/safe?act=vzlom1" method="post">';
                echo 'Введите комбинацию цифр:<br />';
                echo '<input type="text" size="1" maxlength="1" name="k1"/>';
                echo '<input type="text" size="1" maxlength="1" name="k2"/>';
                echo '<input type="text" size="1" maxlength="1" name="k3"/>';
                echo '<input type="text" size="1" maxlength="1" name="k4"/>';
                echo '<input type="submit" value="Лoмaть"/></form><br />';
            }
        }
    }

break;


case 'vzlom1':
    $k1 = isset($_POST['k1']) ? intval($_POST['k1']) : 0;
    $k2 = isset($_POST['k2']) ? intval($_POST['k2']) : 0;
    $k3 = isset($_POST['k3']) ? intval($_POST['k3']) : 0;
    $k4 = isset($_POST['k4']) ? intval($_POST['k4']) : 0;

    if ($k1==$_SESSION['s1'] || $k1==$_SESSION['s2'] || $k1==$_SESSION['s3'] || $k1==$_SESSION['s4'] ){$g1="*";}
    else {$g1="-";}
    if ($k2==$_SESSION['s1'] || $k2==$_SESSION['s2'] || $k2==$_SESSION['s3'] || $k2==$_SESSION['s4'] ){$g2="*";}
    else {$g2="-";}
    if ($k3==$_SESSION['s1'] || $k3==$_SESSION['s2'] || $k3==$_SESSION['s3'] || $k3==$_SESSION['s4'] ){$g3="*";}
    else {$g3="-";}
    if ($k4==$_SESSION['s1'] || $k4==$_SESSION['s2'] || $k4==$_SESSION['s3'] || $k4==$_SESSION['s4'] ){$g4="*";}
    else {$g4="-";}
    if ($k1==$_SESSION['s1']){$g1=$_SESSION['s1'];}
    if ($k2==$_SESSION['s2']){$g2=$_SESSION['s2'];}
    if ($k3==$_SESSION['s3']){$g3=$_SESSION['s3'];}
    if ($k4==$_SESSION['s4']){$g4=$_SESSION['s4'];}

    if (empty($_SESSION['go'])){
        echo'<br /><font color="red">Нее... такой фокус тут не канает...</font><br />';
        notice('нееее... такое тут не канает!');
        redirect("/games/safe");exit;
    }else{

        $_SESSION['try']--;

        $d1="-"; $d2="-"; $d3="-"; $d4="-";
        if ($k1==$_SESSION['s2']){$d2="x";}
        if ($k1==$_SESSION['s3']){$d3="x";}
        if ($k1==$_SESSION['s4']){$d4="x";}
        if ($k1==$_SESSION['s2'] && $k1==$_SESSION['s3']){$d2="x";$d3="x";}
        if ($k1==$_SESSION['s2'] && $k1==$_SESSION['s4']){$d2="x";$d4="x";}
        if ($k1==$_SESSION['s4'] && $k1==$_SESSION['s3']){$d4="x";$d3="x";}
        if ($k1==$_SESSION['s2'] && $k1==$_SESSION['s3'] && $k1==$_SESSION['s4']){$d2="x";$d3="x";$d4="x";}
        if ($k2==$_SESSION['s1']){$d1="x";}
        if ($k2==$_SESSION['s3']){$d3="x";}
        if ($k2==$_SESSION['s4']){$d4="x";}
        if ($k2==$_SESSION['s1'] && $k2==$_SESSION['s3']){$d1="x";$d3="x";}
        if ($k2==$_SESSION['s2'] && $k2==$_SESSION['s4']){$d1="x";$d4="x";}
        if ($k2==$_SESSION['s4'] && $k2==$_SESSION['s3']){$d4="x";$d3="x";}
        if ($k2==$_SESSION['s1'] && $k2==$_SESSION['s3'] && $k2==$_SESSION['s4']){$d1="x";$d3="x";$d4="x";}
        if ($k3==$_SESSION['s1']){$d1="x";}
        if ($k3==$_SESSION['s2']){$d2="x";}
        if ($k3==$_SESSION['s4']){$d4="x";}
        if ($k3==$_SESSION['s1'] && $k3==$_SESSION['s2']){$d1="x";$d2="x";}
        if ($k3==$_SESSION['s2'] && $k3==$_SESSION['s4']){$d1="x";$d4="x";}
        if ($k3==$_SESSION['s4'] && $k3==$_SESSION['s2']){$d4="x";$d2="x";}
        if ($k3==$_SESSION['s1'] && $k3==$_SESSION['s2'] && $k3==$_SESSION['s4']){$d1="x";$d2="x";$d4="x";}
        if ($k4==$_SESSION['s1']){$d1="x";}
        if ($k4==$_SESSION['s2']){$d2="x";}
        if ($k4==$_SESSION['s3']){$d3="x";}
        if ($k4==$_SESSION['s1'] && $k4==$_SESSION['s2']){$d1="x";$d2="x";}
        if ($k4==$_SESSION['s2'] && $k4==$_SESSION['s3']){$d1="x";$d3="x";}
        if ($k4==$_SESSION['s3'] && $k4==$_SESSION['s2']){$d3="x";$d2="x";}
        if ($k4==$_SESSION['s1'] && $k4==$_SESSION['s2'] && $k4==$_SESSION['s4']){$d1="x";$d2="x";$d3="x";}
        if ($k1==$_SESSION['s1']){$d1=$_SESSION['s1'];}
        if ($k2==$_SESSION['s2']){$d2=$_SESSION['s2'];}
        if ($k3==$_SESSION['s3']){$d3=$_SESSION['s3'];}
        if ($k4==$_SESSION['s4']){$d4=$_SESSION['s4'];}

        if ($k1==$_SESSION['s1'] && $k2==$_SESSION['s2'] && $k3==$_SESSION['s3'] && $k4==$_SESSION['s4']) {
            echo '<img src="/images/safe/safe-open.png" alt="сейф"/><br />';
            echo '<br />ПОЗДРАВЛЯЮ! СЕЙФ УСПЕШНО ВЗЛОМАН!<br />
            <font color="red">НА ВАШ СЧЁТ ПЕРЕВЕДЕНЫ 1000$</font><br />';

            DB::run() -> query("UPDATE `users` SET `users_money`=`users_money`+? WHERE `users_login`=? LIMIT 1;", array($config['safesum'], $log));
            unset($_SESSION['go'], $_SESSION['try']);

            echo'&raquo; <a href="/games/safe">Ещё взломать?</a><br /><br />';
        } else {

            if (empty($_SESSION['try'])) {
                echo '<img src="/images/safe/safe-closed.png" alt="сейф"/><br />';
                echo '<font color="red">Щифp был:</font><br />';
                echo '<b>'.$_SESSION['s1'].'-'.$_SESSION['s2'].'-'.$_SESSION['s3'].'-'.$_SESSION['s4'].'</b>';

                echo '<br />Попытки закончились. A взломать сейф так и не получилось...
                Возможно, в другой раз тебе повезёт больше...<br />';
                echo '<br />&raquo; <a href="/games/safe">Ещё разок!</a><br /><br />';
                unset($_SESSION['go'], $_SESSION['try']);
            } else {

                echo '<img src="/images/safe/safe-closed.png" alt="сейф"/><br />';
                echo ''.nickname($log).', не торопись! Просто хорошо подумай. <br />';
                echo 'Попыток осталось: <font color="red"><big>'.$_SESSION['try'].'</big></font><br />';
                echo 'Комбинация сейфа:<br />';
                echo '<b><font color="red">'.$d1.' '.$d2.' '.$d3.' '.$d4.'</font></b><br />';

                echo'<form action="/games/safe?act=vzlom1" method="post">
                Введите комбинацию цифр:<br />
                <input type="text" size="1" maxlength="1" name="k1" value="'.$k1.'" />
                <input type="text" size="1" maxlength="1" name="k2" value="'.$k2.'" />
                <input type="text" size="1" maxlength="1" name="k3" value="'.$k3.'" />
                <input type="text" size="1" maxlength="1" name="k4" value="'.$k4.'" />
                <input type="submit" value="Лoмaть"/></form>';

                echo '<hr />Справка:<br />1. символ <b>-</b> означает, что введённая цифра отсутствует в коде сейфа.<br />
                2. символ <big>*</big> означает, что цифра, которую вы ввели есть, но стоит на другом месте в шифре сейфа.<br />
                3. символ <b>х</b> означает, что хотябы одна из угаданных вами цифр присутствует в шифре сейфа, и стоит на месте <b>х</b>.<br /><br />';
            }
        }
    }

break;

endswitch;

echo '<img src="/assets/img/images/games.gif" alt="image" /> <a href="/games">Развлечения</a><br />';

App::view($config['themes'].'/foot');
