<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Classes\Validator;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class MailController extends BaseController
{
    /**
     * Главная страница
     *
     * @param Request   $request
     * @param Validator $validator
     * @return string
     */
    public function index(Request $request, Validator $validator): string
    {
        if ($request->isMethod('post')) {

            $message = nl2br(check($request->input('message')));
            $name    = check($request->input('name'));
            $email   = check($request->input('email'));

            if (getUser()) {
                $name = getUser('login');
                $email = getUser('email');
            }

            $validator->true(captchaVerify(), ['protect' => trans('validator.captcha')])
                ->length($name, 5, 100, ['name' => 'Слишком длинное или короткое имя'])
                ->length($message, 5, 50000, ['message' => trans('validator.text')])
                ->email($email, ['email' => 'Неправильный адрес email, необходим формат name@site.domen!']);

            if ($validator->isValid()) {

                $message .= '<br><br>IP: ' . getIp() . '<br>Браузер: ' . getBrowser() . '<br>Отправлено: ' . dateFixed(SITETIME, 'j.m.Y / H:i');

                $subject = 'Письмо с сайта ' . setting('title');
                $body = view('mailer.default', compact('subject', 'message'));
                sendMail(env('SITE_EMAIL'), $subject, $body, ['from' => [$email => $name]]);

                setFlash('success', 'Ваше письмо успешно отправлено!');
                redirect('/');
            } else {
                setInput($request->all());
                setFlash('danger', $validator->getErrors());
            }
        }

        return view('mails/index');
    }

    /**
     * Восстановление пароля
     *
     * @param Request   $request
     * @param Validator $validator
     * @return string
     */
    public function recovery(Request $request, Validator $validator): string
    {
        if (getUser()) {
            abort('default', 'Вы авторизованы, восстановление пароля невозможно!');
        }

        $cookieLogin = isset($_COOKIE['login']) ? check($_COOKIE['login']) : '';

        if ($request->isMethod('post')) {
            $login = check($request->input('user'));

            $user = User::query()->where('login', $login)->orWhere('email', $login)->first();
            if (! $user) {
                abort('default', trans('validator.user'));
            }

            $validator->true(captchaVerify(), ['protect' => trans('validator.captcha')])
                ->lte($user['timepasswd'], SITETIME, ['user' => 'Восстанавливать пароль можно не чаще чем раз в 12 часов!']);

            if ($validator->isValid()) {
                $resetKey  = Str::random();
                $resetLink = siteUrl(true) . '/restore?key=' . $resetKey;

                $user->update([
                    'keypasswd'  => $resetKey,
                    'timepasswd' => SITETIME + 3600,
                ]);

                //Инструкция по восстановлению пароля на email
                $subject = 'Восстановление пароля на сайте ' . setting('title');
                $message = 'Здравствуйте, ' . $user['login'] . '<br>Вами была произведена операция по восстановлению пароля на сайте <a href="' . siteUrl(true) . '">' . setting('title') . '</a><br><br>Данные отправителя:<br>Ip: ' . getIp() . '<br>Браузер: ' . getBrowser() . '<br>Отправлено: ' . date('j.m.Y / H:i', SITETIME) . '<br><br>Для того чтобы восстановить пароль, вам необходимо нажать на кнопку восстановления<br>Если это письмо попало к вам по ошибке или вы не собираетесь восстанавливать пароль, то просто проигнорируйте его';

                $body = view('mailer.recovery', compact('subject', 'message', 'resetLink'));
                sendMail($user['email'], $subject, $body);

                setFlash('success', 'Инструкция по восстановлению пароля отправлена на ' . hideMail($user['email']) . '!');
                redirect('/login');
            } else {
                setInput($request->all());
                setFlash('danger', $validator->getErrors());
            }
        }

        return view('mails/recovery', compact('cookieLogin'));
    }

    /**
     * Восстановление пароля
     *
     * @param Request   $request
     * @param Validator $validator
     * @return string
     */
    public function restore(Request $request, Validator $validator): ?string
    {
        if (getUser()) {
            abort(403, 'Вы авторизованы, восстановление пароля невозможно!');
        }

        $key = check($request->input('key'));

        $user = User::query()->where('keypasswd', $key)->first();
        if (! $user) {
            abort('default', 'Ключ для восстановления недействителен!');
        }

        $validator->notEmpty($key, 'Отсутствует секретный код в ссылке для восстановления пароля!')
            ->notEmpty($user['keypasswd'], 'Данный пользователь не запрашивал восстановление пароля!')
            ->gte($user['timepasswd'], SITETIME, 'Секретный ключ для восстановления уже устарел!');

        if ($validator->isValid()) {

            $newpass    = Str::random();
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
        }

        setFlash('danger', current($validator->getErrors()));
        redirect('/');
    }

    /**
     * Отписка от рассылки
     *
     * @param Request $request
     */
    public function unsubscribe(Request $request): void
    {
        $key = check($request->input('key'));

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
