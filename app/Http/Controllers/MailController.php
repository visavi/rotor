<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Classes\Validator;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class MailController extends Controller
{
    /**
     * Главная страница
     */
    public function index(Request $request, Validator $validator): View|RedirectResponse
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
     * Отписка от рассылки
     */
    public function unsubscribe(Request $request): RedirectResponse
    {
        $key = $request->input('key');

        if (! $key) {
            abort(200, __('mails.token_missing'));
        }

        $user = User::query()->where('subscribe', $key)->first();

        if (! $user) {
            abort(200, __('mails.token_expired'));
        }

        $user->subscribe = null;
        $user->save();

        setFlash('success', __('mails.success_unsubscribed'));

        return redirect('/');
    }
}
