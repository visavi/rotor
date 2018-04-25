<?php

namespace App\Controllers;

use App\Classes\Request;
use App\Classes\Validator;
use App\Models\User;

class MailController extends BaseController
{
    /**
     * Главная страница
     */
    public function index()
    {
        if (Request::isMethod('post')) {

            $message = nl2br(check(Request::input('message')));
            $name    = check(Request::input('name'));
            $email   = check(Request::input('email'));

            if (getUser()) {
                $name = getUser('login');
                $email = getUser('email');
            }

            $validator = new Validator();
            $validator->true(captchaVerify(), ['protect' => 'Не удалось пройти проверку captcha!'])
                ->length($name, 5, 100, ['name' => 'Слишком длинное или короткое имя'])
                ->length($message, 5, 50000, ['message' => 'Слишком длинное или короткое сообшение'])
                ->email($email, ['email' => 'Неправильный адрес email, необходим формат name@site.domen!']);

            if ($validator->isValid()) {

                $message .= '<br><br>IP: ' . getIp() . '<br>Браузер: ' . getBrowser() . '<br>Отправлено: ' . dateFixed(SITETIME, 'j.m.Y / H:i');

                $subject = 'Письмо с сайта ' . setting('title');
                $body = view('mailer.default', compact('subject', 'message'));
                sendMail(env('SITE_EMAIL'), $subject, $body, ['from' => [$email => $name]]);

                setFlash('success', 'Ваше письмо успешно отправлено!');
                redirect('/');
            } else {
                setInput(Request::all());
                setFlash('danger', $validator->getErrors());
            }
        }

        return view('mails/index');
    }

    /**
     * Восстановление пароля
     */
    public function recovery()
    {
        if (getUser()) {
            abort('default', 'Вы авторизованы, восстановление пароля невозможно!');
        }

        $cookieLogin = (isset($_COOKIE['login'])) ? check($_COOKIE['login']) : '';

        if (Request::isMethod('post')) {
            $login = check(Request::input('user'));

            $user = User::query()->where('login', $login)->orWhere('email', $login)->first();
            if (! $user) {
                abort('default', 'Пользователь с данным логином или email не найден!');
            }

            $validator = new Validator();
            $validator->true(captchaVerify(), ['protect' => 'Не удалось пройти проверку captcha!'])
                ->lte($user['timepasswd'], SITETIME, ['user' => 'Восстанавливать пароль можно не чаще чем раз в 12 часов!']);

            if ($validator->isValid()) {
                $resetKey  = str_random();
                $resetLink = siteUrl(true) . '/recovery/restore?key=' . $resetKey;

                $user->update([
                    'keypasswd'  => $resetKey,
                    'timepasswd' => SITETIME + 43200,
                ]);

                //Инструкция по восстановлению пароля на email
                $subject = 'Восстановление пароля на сайте ' . setting('title');
                $message = 'Здравствуйте, ' . $user['login'] . '<br>Вами была произведена операция по восстановлению пароля на сайте <a href="' . siteUrl(true) . '">' . setting('title') . '</a><br><br>Данные отправителя:<br>Ip: ' . getIp() . '<br>Браузер: ' . getBrowser() . '<br>Отправлено: ' . date('j.m.Y / H:i', SITETIME) . '<br><br>Для того чтобы восстановить пароль, вам необходимо нажать на кнопку восстановления<br>Если это письмо попало к вам по ошибке или вы не собираетесь восстанавливать пароль, то просто проигнорируйте его';

                $body = view('mailer.recovery', compact('subject', 'message', 'resetLink'));
                sendMail($user['email'], $subject, $body);

                setFlash('success', 'Восстановление пароля инициализировано!');
                redirect('/recovery');
            } else {
                setInput(Request::all());
                setFlash('danger', $validator->getErrors());
            }
        }

        return view('mails/recovery', compact('cookieLogin'));
    }

    /**
     * Восстановление пароля
     */
    public function restore()
    {
        if (getUser()) {
            abort(403, 'Вы авторизованы, восстановление пароля невозможно!');
        }

        $key = check(Request::input('key'));

        $user = User::query()->where('keypasswd', $key)->first();
        if (! $user) {
            abort('default', 'Ключ для восстановления недействителен!');
        }

        $validator = new Validator();
        $validator->notEmpty($key, 'Отсутствует секретный код в ссылке для восстановления пароля!')
            ->notEmpty($user['keypasswd'], 'Данный пользователь не запрашивал восстановление пароля!')
            ->gte($user['timepasswd'], SITETIME, 'Секретный ключ для восстановления уже устарел!');

        if ($validator->isValid()) {

            $newpass    = str_random();
            $hashnewpas = password_hash($newpass, PASSWORD_BCRYPT);

            $user->update([
                'password'   => $hashnewpas,
                'keypasswd'  => null,
                'timepasswd' => 0,
            ]);

            // Восстановление пароля на email
            $subject = 'Восстановление пароля на сайте ' . setting('title');
            $message = 'Здравствуйте, ' . $user['login'] . '<br>Ваши новые данные для входа на на сайт <a href="' . siteUrl(true) . '">' . setting('title') . '</a><br><b>Логин: ' . $user['login'] . '</b><br><b>Пароль: ' . $newpass . '</b><br><br>Запомните и постарайтесь больше не забывать данные <br>Пароль вы сможете поменять в своем профиле<br>Всего наилучшего!';

            $body = view('mailer.default', compact('subject', 'message'));
            sendMail($user['email'], $subject, $body);

            return view('mails/restore', ['login' => $user['login'], 'password' => $newpass]);
        } else {
            setFlash('danger', current($validator->getErrors()));
            redirect('/');
        }
    }

    /**
     * Отписка от рассылки
     */
    public function unsubscribe()
    {
        $key = check(Request::input('key'));

        if (! $key) {
            abort('default', 'Отсутствует ключ для отписки от рассылки');
        }

        $user = User::query()->where('subscribe', $key)->first();

        if (! $user) {
            abort('default', 'Ключ для отписки от рассылки устарел!');
        }

        $user->subscribe = null;
        $user->save();

        setFlash('success', 'Вы успешно отписались от рассылки!');
        redirect('/');
    }
}
