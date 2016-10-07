<?php
App::view($config['themes'].'/index');

$act = (isset($_GET['act'])) ? check($_GET['act']) : 'index';

show_title('Восстановление пароля');

if (! is_user()) {
switch ($act):
############################################################################################
##                                    Главная страница                                    ##
############################################################################################
case 'index':

    $cooklog = (isset($_COOKIE['cooklog'])) ? check($_COOKIE['cooklog']): '';

    echo 'Инструкция по восстановлению будет выслана на электронный адрес указанный в профиле<br />';
    echo 'Восстанавливать пароль можно не чаще чем раз в 12 часов<br /><br />';

    echo '<div class="form">';
    echo '<form method="post" action="/lostpassword?act=remind">';
    echo 'Логин, ник или email:<br />';
    echo '<input name="uz" type="text" maxlength="20" value="'.$cooklog.'" /><br />';
    echo '<input value="Продолжить" type="submit" /></form></div><br />';

    echo 'Если у вас установлен секретный вопрос, вам будет предложено на него ответить<br /><br />';

break;

############################################################################################
##                                  Восстановление пароля                                 ##
############################################################################################
case 'remind':

    $uz = check(utf_lower(strval($_REQUEST['uz'])));

    if (!empty($uz)) {

        $user = DB::run() -> queryFetch("SELECT * FROM `users` WHERE LOWER(`users_login`)=? OR `users_email`=? OR LOWER(`users_nickname`)=? LIMIT 1;", array($uz, $uz, $uz));

        if (!empty($user)) {

            $email = ($uz == $user['users_email']) ? $user['users_email'] : '';

            echo '<div class="b">'.user_gender($user['users_login']).' <b>'.profile($user['users_login']).'</b> '.user_visit($user['users_login']).'</div>';

            if (!empty($user['users_email'])) {
                echo '<b><big>Восстановление на e-mail:</big></b><br />';
                echo '<div class="form">';
                echo '<form method="post" action="/lostpassword?act=send">';
                echo 'Введите e-mail:<br />';
                echo '<input name="email" type="text" value="'.$email.'" maxlength="50" /><br />';
                echo 'Проверочный код:<br /> ';
                echo '<img src="/captcha" alt="" /><br />';
                echo '<input name="provkod" size="6" maxlength="6" /><br />';
                echo '<input name="uz" type="hidden" value="'.$user['users_login'].'" />';
                echo '<br /><input value="Восстановить" type="submit" /></form></div><br />';
            }
            // --------------------------------------------------------------//
            if (!empty($user['users_secquest'])) {
                echo '<b><big>Ответ на секретный вопрос:</big></b><br />';
                echo '<div class="form">';
                echo '<form method="post" action="/lostpassword?act=answer">';

                echo $user['users_secquest'].'<br />';
                echo '<input name="answer" type="text" maxlength="30" /><br />';

                echo 'Проверочный код:<br /> ';
                echo '<img src="/captcha" alt="" /><br />';
                echo '<input name="provkod" size="6" maxlength="6" /><br />';
                echo '<input name="uz" type="hidden" value="'.$user['users_login'].'" />';
                echo '<br /><input value="Восстановить" type="submit" /></form></div><br />';
            }

            if (empty($user['users_email']) && empty($user['users_secquest'])) {
                echo '<img src="/images/img/error.gif" alt="image" /> <b>Невозможно восстановить пароль!</b><br />';
                echo 'Нет технической возможности восстановить пароль, так как у данного пользователя не указан адрес почтового ящика и не установлен секретный вопрос<br />';
                echo 'Для того чтобы вернуть доступ к своему аккаунту необходимо связаться с администрацией сайта<br /><br />';
            }
        } else {
            show_error('Ошибка! Пользователь с данным логином или email не найден!');
        }
    } else {
        show_error('Ошибка! Вы не ввели логин или email пользователя для восстановления!');
    }

    echo '<img src="/images/img/back.gif" alt="image" /> <a href="/lostpassword">Вернуться</a><br />';
break;

############################################################################################
##                            Подтверждение восстановления                                ##
############################################################################################
case 'send':

    $uz = check(strval($_POST['uz']));
    $email = check(strval($_POST['email']));
    $provkod = check($_POST['provkod']);

    $user = DB::run() -> queryFetch("SELECT * FROM `users` WHERE `users_login`=? LIMIT 1;", array($uz));
    if (! empty($user)) {

        $validation = new Validation();

        $validation -> addRule('equal', array($provkod, $_SESSION['protect']), 'Проверочное число не совпало с данными на картинке!')
            -> addRule('not_empty', $email, 'Не введен адрес почтового ящика для восстановления!')
            -> addRule('not_empty', $user['users_email'], 'У данного пользователя не установлен email!')
            -> addRule('equal', array($email, $user['users_email']), 'Введенный адрес email не совпадает с адресом в профиле!')
            -> addRule('min', array($user['users_timepasswd'], SITETIME), 'Восстанавливать пароль можно не чаще чем раз в 12 часов!');

        if ($validation->run()) {

            $restkey = generate_password();

            DB::run() -> query("UPDATE `users` SET `users_keypasswd`=?, `users_timepasswd`=? WHERE `users_login`=?;", array($restkey, SITETIME + 43200, $uz));
            // ---------------- Инструкция по восстановлению пароля на E-mail --------------------------//
            sendMail($user['users_email'],
                'Подтверждение восстановления пароля на сайте '.$config['title'],
                nl2br("Здравствуйте, ".nickname($user['users_login'])." \nВами была произведена операция по восстановлению пароля на сайте ".$config['home']." \n\nДанные отправителя: \nIp: ".App::getClientIp()." \nБраузер: ".App::getUserAgent()." \nОтправлено: ".date('j.m.Y / H:i', SITETIME)."\n\nДля того чтобы восстановить пароль, вам необходимо перейти по ссылке: \n\n".$config['home']."/lostpassword?act=restore&uz=".$user['users_login']."&key=".$restkey." \n\nЕсли это письмо попало к вам по ошибке или вы не собираетесь восстанавливать пароль, то просто проигнорируйте его")
            );

            echo '<img src="/images/img/open.gif" alt="image" /> <b>Восстановление пароля инициализировано!</b><br /><br />';
            echo 'Письмо с инструкцией по восстановлению пароля успешно выслано на E-mail указанный в профиле<br />';
            echo 'Внимательно прочтите письмо и выполните все необходимые действия для восстановления пароля<br />';
            echo 'Восстанавливать пароль можно не чаще чем раз в 12 часов<br /><br />';

        } else {
            show_error($validation->getErrors());
        }
    } else {
        show_error('Ошибка! Пользователь с данным логином не найден!');
    }

    echo '<img src="/images/img/back.gif" alt="image" /> <a href="/lostpassword?act=remind&amp;uz='.$uz.'">Вернуться</a><br />';
break;

############################################################################################
##                                Восстановление пароля                                   ##
############################################################################################
case 'restore':

    $uz = isset($_GET['uz']) ? check($_GET['uz']) : '';
    $key = isset($_GET['key']) ? check($_GET['key']) : '';

    $user = DB::run() -> queryFetch("SELECT * FROM `users` WHERE `users_login`=? LIMIT 1;", array($uz));
    if (!empty($user)) {

        $validation = new Validation();

        $validation -> addRule('not_empty', $key, 'Отсутствует секретный код в ссылке для восстановления пароля!')
            -> addRule('not_empty', $user['users_keypasswd'], 'Данный пользователь не запрашивал восстановление пароля!')
            -> addRule('equal', array($key, $user['users_keypasswd']), 'Секретный код в ссылке не совпадает с данными в профиле!')
            -> addRule('max', array($user['users_timepasswd'], SITETIME), 'Секретный ключ для восстановления уже устарел!');

        if ($validation->run()) {

            $newpass = generate_password();
            $mdnewpas = md5(md5($newpass));

            DB::run() -> query("UPDATE `users` SET `users_pass`=?, `users_keypasswd`=?, `users_timepasswd`=? WHERE `users_login`=?;", array($mdnewpas, '', 0, $uz));

            echo '<b>Пароль успешно восстановлен!</b><br />';
            echo 'Ваши новые данные для входа на сайт<br /><br />';

            echo 'Логин: <b>'.$user['users_login'].'</b><br />';
            echo 'Пароль: <b>'.$newpass.'</b><br /><br />';

            echo 'Запомните и постарайтесь больше не забывать данные<br /><br />';

            echo 'Пароль вы сможете поменять в своем профиле<br /><br />';

            // --------------------------- Восстановлению пароля на E-mail --------------------------//
            sendMail($user['users_email'],
                'Восстановление пароля на сайте '.$config['title'],
                nl2br("Здравствуйте, ".nickname($user['users_login'])." \nВаши новые данные для входа на на сайт ".$config['home']." \nЛогин: ".$user['users_login']." \nПароль: ".$newpass." \n\nЗапомните и постарайтесь больше не забывать данные \nПароль вы сможете поменять в своем профиле \nВсего наилучшего!")
            );

        } else {
            show_error($validation->getErrors());
        }
    } else {
        show_error('Ошибка! Пользователь с данным логином не найден!');
    }
    echo '<img src="/images/img/back.gif" alt="image" /> <a href="/lostpassword">Вернуться</a><br />';
break;

############################################################################################
##                            Ответ на секретный вопрос                                   ##
############################################################################################
case 'answer':

    $uz = check(strval($_POST['uz']));
    $answer = check(strval($_POST['answer']));
    $provkod = check($_POST['provkod']);

    $user = DB::run() -> queryFetch("SELECT * FROM `users` WHERE `users_login`=? LIMIT 1;", array($uz));
    if (!empty($user)) {

        $validation = new Validation();

        $validation -> addRule('equal', array($provkod, $_SESSION['protect']), 'Проверочное число не совпало с данными на картинке!')
            -> addRule('not_empty', $answer, 'Не введен ответ на секретный вопрос для восстановления!')
            -> addRule('not_empty', $user['users_secquest'], 'У данного пользователя не установлен секретный вопрос!')
            -> addRule('equal', array(md5(md5($answer)), $user['users_secanswer']), 'Ответ на секретный вопрос не совпадает с данными в профиле!');

        if ($validation->run()) {

            $newpass = generate_password();
            $mdnewpas = md5(md5($newpass));

            DB::run() -> query("UPDATE `users` SET `users_pass`=?, `users_keypasswd`=?, `users_timepasswd`=? WHERE `users_login`=?;", array($mdnewpas, '', 0, $uz));

            echo '<b>Пароль успешно восстановлен!</b><br />';
            echo 'Ваши новые данные для входа на сайт<br /><br />';

            echo 'Логин: <b>'.$user['users_login'].'</b><br />';
            echo 'Пароль: <b>'.$newpass.'</b><br /><br />';

            echo 'Запомните и постарайтесь больше не забывать данные<br /><br />';

            echo 'Пароль вы сможете поменять в своем профиле<br /><br />';

        } else {
            show_error($validation->getErrors());
        }
    } else {
        show_error('Ошибка! Пользователь с данным логином не найден!');
    }

    echo '<img src="/images/img/back.gif" alt="image" /> <a href="/lostpassword?act=remind&amp;uz='.$uz.'">Вернуться</a><br />';
break;

endswitch;

} else {
    show_error('Ошибка! Вы авторизованы, восстановление пароля невозможно!');
}

App::view($config['themes'].'/foot');
