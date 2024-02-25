<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Classes\Validator;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\View\View;

class MailController extends Controller
{
    /**
     * Главная страница
     *
     *
     * @return View|RedirectResponse
     */
    public function index(Request $request, Validator $validator)
    {
        if ($request->isMethod('post')) {
            $message = $request->input('message');
            $name = $request->input('name');
            $email = $request->input('email');

            if ($user = getUser()) {
                $name = $user->login;
                $email = $user->email;
            }

            $validator->true(captchaVerify(), ['protect' => __('validator.captcha')])
                ->length($name, 3, 100, ['name' => __('mails.name_short_or_long')])
                ->length($message, 5, 50000, ['message' => __('validator.text')])
                ->email($email, ['email' => __('validator.email')]);

            if ($validator->isValid()) {
                $subject = __('mails.email_from_site', ['sitename' => setting('title')], setting('language'));

                $message = str_replace(
                    '/uploads/stickers',
                    config('app.url') . '/uploads/stickers',
                    bbCode($message)->toHtml()
                );

                $message .= '<br><br>Email: ' . $name . ' &lt;' . $email . '&gt;<br>IP: ' . getIp() . '<br>Browser: ' . getBrowser() . '<br>' . __('main.sent_out', [], setting('language')) . ': ' . dateFixed(SITETIME, 'd.m.y / H:i');
                $data = [
                    'to'      => config('app.email'),
                    'subject' => $subject,
                    'text'    => $message,
                    'from'    => [$email, $name],
                ];

                $send = sendMail('mailer.default', $data);

                if ($send) {
                    setFlash('success', __('mails.success_sent'));
                } else {
                    setFlash('danger', __('mails.failed_sent'));
                }

                return redirect('/mails');
            }

            setInput($request->all());
            setFlash('danger', $validator->getErrors());
        }

        return view('mails/index');
    }

    /**
     * Восстановление пароля
     *
     *
     * @return View|RedirectResponse
     */
    public function recovery(Request $request, Validator $validator)
    {
        if (getUser()) {
            setFlash('danger', __('main.already_authorized'));

            return redirect('/');
        }

        $cookieLogin = $request->cookie('login');

        if ($request->isMethod('post')) {
            $user = getUserByLoginOrEmail($request->input('user'));
            if (! $user) {
                abort(200, __('validator.user'));
            }

            $validator->true(captchaVerify(), ['protect' => __('validator.captcha')])
                ->lte($user->timepasswd, SITETIME, ['user' => __('mails.password_recovery_time')]);

            if ($validator->isValid()) {
                $resetKey = Str::random();
                $resetLink = config('app.url') . '/restore?key=' . $resetKey;

                $user->update([
                    'keypasswd'  => $resetKey,
                    'timepasswd' => strtotime('+1 hour', SITETIME),
                ]);

                //Инструкция по восстановлению пароля на email
                $subject = 'Восстановление пароля на ' . setting('title');
                $message = 'Здравствуйте, ' . $user->getName() . '<br>Вами была произведена операция по восстановлению пароля на сайте <a href="' . config('app.url') . '">' . setting('title') . '</a><br><br>Данные отправителя:<br>Ip: ' . getIp() . '<br>Браузер: ' . getBrowser() . '<br>Отправлено: ' . date('j.m.Y / H:i', SITETIME) . '<br><br>Для того чтобы восстановить пароль, вам необходимо нажать на кнопку восстановления<br>Если это письмо попало к вам по ошибке или вы не собираетесь восстанавливать пароль, то просто проигнорируйте его';

                $data = [
                    'to'        => $user->email,
                    'subject'   => $subject,
                    'text'      => $message,
                    'resetLink' => $resetLink,
                ];

                sendMail('mailer.recovery', $data);
                setFlash('success', __('mails.recovery_instructions', ['email' => hideMail($user->email)]));

                return redirect('login');
            }

            setInput($request->all());
            setFlash('danger', $validator->getErrors());
        }

        return view('mails/recovery', compact('cookieLogin'));
    }

    /**
     * Восстановление пароля
     *
     *
     * @return View|RedirectResponse
     */
    public function restore(Request $request, Validator $validator)
    {
        if (getUser()) {
            setFlash('danger', __('main.already_authorized'));

            return redirect('/');
        }

        $key = $request->input('key');

        /** @var User $user */
        $user = User::query()->where('keypasswd', $key)->first();
        if (! $user) {
            abort(200, __('mails.secret_key_invalid'));
        }

        $validator->notEmpty($key, __('mails.secret_key_missing'))
            ->notEmpty($user->keypasswd, __('mails.password_not_recovery'))
            ->gte($user->timepasswd, SITETIME, __('mails.secret_key_expired'));

        if ($validator->isValid()) {
            $newpass = Str::random();
            $hashnewpas = password_hash($newpass, PASSWORD_BCRYPT);

            $user->update([
                'password'   => $hashnewpas,
                'keypasswd'  => null,
                'timepasswd' => 0,
            ]);

            // Восстановление пароля на email
            $subject = 'Восстановление пароля на ' . setting('title');
            $message = 'Здравствуйте, ' . $user->getName() . '<br>Ваши новые данные для входа на на сайт <a href="' . config('app.url') . '">' . setting('title') . '</a><br><b>Логин: ' . $user->login . '</b><br><b>Пароль: ' . $newpass . '</b><br><br>Запомните и постарайтесь больше не забывать данные <br>Пароль вы сможете поменять в своем профиле<br>Всего наилучшего!';

            $data = [
                'to'      => $user->email,
                'subject' => $subject,
                'text'    => $message,
            ];

            sendMail('mailer.default', $data);

            return view('mails/restore', ['login' => $user->login, 'password' => $newpass]);
        }

        setFlash('danger', current($validator->getErrors()));

        return redirect('/');
    }

    /**
     * Отписка от рассылки
     */
    public function unsubscribe(Request $request): RedirectResponse
    {
        $key = $request->input('key');

        if (! $key) {
            abort(200, __('mails.secret_key_missing'));
        }

        $user = User::query()->where('subscribe', $key)->first();

        if (! $user) {
            abort(200, __('mails.secret_key_expired'));
        }

        $user->subscribe = null;
        $user->save();

        setFlash('success', __('mails.success_unsubscribed'));
        return redirect('/');
    }
}
