<?php

if (! is_user()) {
    App::abort(403, 'Авторизуйтесь для изменения данных в профиле!');
}

if (Request::isMethod('post')) {

    $token = check(Request::input('token'));
    $info = check(Request::input('info'));
    $name = check(Request::input('name'));
    $country = check(Request::input('country'));
    $city = check(Request::input('city'));
    $icq = check(str_replace('-', '', Request::input('icq')));
    $skype = check(strtolower(Request::input('skype')));
    $site = check(Request::input('site'));
    $birthday = check(Request::input('birthday'));

    $validation = new Validation();

    $validation->addRule('equal', [$token, $_SESSION['token']], 'Неверный идентификатор сессии, повторите действие!')
        ->addRule('regex', [$site, '#^https?://([а-яa-z0-9_\-\.])+(\.([а-яa-z0-9\/])+)+$#u'], ['site' => 'Недопустимый адрес сайта, необходим формата http://my_site.domen!'], false)
        ->addRule('regex', [$birthday, '#^[0-9]{2}+\.[0-9]{2}+\.[0-9]{4}$#'], ['birthday' => 'Недопустимый формат даты рождения, необходим формат дд.мм.гггг!'], false)
        ->addRule('regex', [$icq, '#^[0-9]{5,10}$#'], ['icq' => 'Недопустимый формат ICQ, только цифры от 5 до 10 символов!'], false)
        ->addRule('regex', [$skype, '#^[a-z]{1}[0-9a-z\_\.\-]{5,31}$#'], ['skype' => 'Недопустимый формат Skype, только латинские символы от 6 до 32!'], false)
        ->addRule('string', $info, ['info' => 'Слишком большая информация о себе, не более 1000 символов!'], true, 0, 1000);

    if ($validation->run()) {

        $name = utf_substr($name, 0, 20);
        $country = utf_substr($country, 0, 30);
        $city = utf_substr($city, 0, 50);

        DB::run()->query("UPDATE `users` SET `name`=?, `country`=?, `city`=?, `icq`=?, `skype`=?, `site`=?, `birthday`=?, `info`=? WHERE `login`=? LIMIT 1;", [$name, $country, $city, $icq, $skype, $site, $birthday, $info, $log]);

        App::setFlash('success', 'Ваш профиль успешно изменен!');
        redirect("/profile");

    } else {
        App::setInput(Request::all());
        App::setFlash('danger', $validation->getErrors());
    }
}

App::view('pages/profile', compact('udata'));
