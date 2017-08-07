<?php

if (Request::isMethod('post')) {

    $message = nl2br(check(Request::input('message')));
    $name    = check(Request::input('name'));
    $email   = check(Request::input('email'));
    $protect = check(Request::input('protect'));

    if (is_user()) {
        $name = App::getUsername();
        $email = App::user('email');
    }

    $validation = new Validation();

    $validation -> addRule('equal', [$protect, $_SESSION['protect']], ['protect' => 'Проверочное число не совпало с данными на картинке!'])
        ->addRule('string', $name, ['name' => 'Слишком длинное или короткое имя'], true, 5, 100)
        ->addRule('string', $message, ['message' => 'Слишком длинное или короткое сообшение'], true, 5, 50000)
        ->addRule('email', $email, ['email' => 'Неправильный адрес email, необходим формат name@site.domen!'], true);

    if ($validation->run()) {

        $message .= '<br /><br />IP: '.App::getClientIp().'<br />Браузер: '.App::getUserAgent().'<br />Отправлено: '.date_fixed(SITETIME, 'j.m.Y / H:i');

        $subject = 'Письмо с сайта '.Setting::get('title');
        $body = App::view('mailer.default', compact('subject', 'message'), true);
        App::sendMail(Setting::get('emails'), $subject, $body, ['from' => [$email => $name]]);

        App::setFlash('success', 'Ваше письмо успешно отправлено!');
        App::redirect("/");

    } else {
        App::setFlash('danger', $validation->getErrors());
    }
}

App::view('mail/index');
