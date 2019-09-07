<?php

declare(strict_types=1);

namespace App\Controllers\User;

use App\Classes\Validator;
use App\Controllers\BaseController;
use App\Models\BlackList;
use App\Models\ChangeMail;
use App\Models\Invite;
use App\Models\Online;
use App\Models\User;
use ErrorException;
use Exception;
use Illuminate\Database\Capsule\Manager as DB;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class UserController extends BaseController
{
    /**
     * Анкета пользователя
     *
     * @param string $login
     *
     * @return string
     */
    public function index(string $login): string
    {
        if (! $user = getUserByLogin($login)) {
            abort(404, 'Пользователя с данным логином не существует!');
        }

        $invite  = Invite::query()->where('invite_user_id', $user->id)->first();
        $user->load('lastBan');

        $adminGroups = User::ADMIN_GROUPS;

        return view('users/user', compact('user', 'invite', 'adminGroups'));
    }

    /**
     * Заметка
     *
     * @param string    $login
     * @param Request   $request
     * @param Validator $validator
     *
     * @return string
     */
    public function note(string $login, Request $request, Validator $validator): string
    {
        if (! isAdmin()) {
            abort(403, 'Данная страница доступна только администрации!');
        }

        if (! $user = getUserByLogin($login)) {
            abort(404, 'Пользователя с данным логином не существует!');
        }

        if ($request->isMethod('post')) {
            $notice = check($request->input('notice'));
            $token  = check($request->input('token'));

            $validator->equal($token, $_SESSION['token'], ['notice' => __('validator.token')])
                ->length($notice, 0, 1000, ['notice' => 'Слишком большая заметка!']);

            if ($validator->isValid()) {
                $user->note()->updateOrCreate([], [
                    'text'         => $notice,
                    'edit_user_id' => getUser('id'),
                    'updated_at'   => SITETIME,
                ]);

                setFlash('success', 'Заметка успешно сохранена!');
                redirect('/users/'.$user->login);

            } else {
                setInput($request->all());
                setFlash('danger', $validator->getErrors());
            }
        }

        return view('users/note', compact('user'));
    }

    /**
     * Регистрация
     *
     * @param Request   $request
     * @param Validator $validator
     *
     * @return string
     * @throws ErrorException
     */
    public function register(Request $request, Validator $validator): string
    {
        if (getUser()) {
            abort(403, 'Вы уже регистрировались, запрещено создавать несколько аккаунтов!');
        }

        if (! setting('openreg')) {
            abort('default', 'Регистрация временно приостановлена, пожалуйста зайдите позже!');
        }

        if ($request->isMethod('post')) {
            if ($request->has('login') && $request->has('password')) {
                $login       = check($request->input('login'));
                $password    = trim($request->input('password'));
                $password2   = trim($request->input('password2'));
                $invite      = setting('invite') ? check($request->input('invite')) : '';
                $email       = strtolower(check($request->input('email')));
                $domain      = utfSubstr(strrchr($email, '@'), 1);
                $gender      = $request->input('gender') === 'male' ? 'male' : 'female';
                $level       = User::USER;
                $activateKey = null;
                $invitation  = null;

                $validator->true(captchaVerify(), ['protect' => __('validator.captcha')])
                    ->regex($login, '|^[a-z0-9\-]+$|i', ['login' => 'Недопустимые символы в логине. Разрешены знаки латинского алфавита, цифры и дефис!'])
                    ->regex(utfSubstr($login, 0, 1), '|^[a-z0-9]+$|i', ['login' => 'Логин должен начинаться с буквы или цифры!'])
                    ->email($email, ['email' => 'Вы ввели неверный адрес email, необходим формат name@site.domen!'])
                    ->length($invite, 12, 15, ['invite' => 'Слишком длинный или короткий пригласительный ключ!'], setting('invite'))
                    ->length($login, 3, 20, ['login' => 'Слишком длинный или короткий логин!'])
                    ->length($password, 6, 20, ['password' => 'Слишком длинный или короткий пароль!'])
                    ->equal($password, $password2, ['password2' => 'Введенные пароли отличаются друг от друга!']);

                if (ctype_digit($password)) {
                    $validator->addError(['password' => 'Запрещен пароль состоящий только из цифр, используйте буквы!']);
                }

                if (substr_count($login, '-') > 2) {
                    $validator->addError(['login' => 'Запрещено использовать в логине слишком много дефисов!']);
                }

                if (! empty($login)) {
                    // Проверка логина на существование
                    $checkLogin = User::query()->where('login', $login)->count();
                    $validator->empty($checkLogin, ['login' => 'Пользователь с данным логином уже зарегистрирован!']);

                    // Проверка логина в черном списке
                    $blackLogin = Blacklist::query()
                        ->where('type', 'login')
                        ->where('value', strtolower($login))
                        ->count();
                    $validator->empty($blackLogin, ['login' => 'Выбранный вами логин занесен в черный список!']);
                }

                // Проверка email на существование
                $checkMail = User::query()->where('email', $email)->count();
                $validator->empty($checkMail, ['email' => 'Указанный вами адрес email уже используется в системе!']);

                // Проверка домена от email в черном списке
                $blackDomain = Blacklist::query()
                    ->where('type', 'domain')
                    ->where('value', $domain)
                    ->count();
                $validator->empty($blackDomain, ['email' => 'Домен от вашего адреса email занесен в черный список!']);

                // Проверка email в черном списке
                $blackMail = Blacklist::query()
                    ->where('type', 'email')
                    ->where('value', $email)
                    ->count();
                $validator->empty($blackMail, ['email' => 'Указанный вами адрес email занесен в черный список!']);

                // Проверка пригласительного ключа
                if (setting('invite')) {
                    $invitation = Invite::query()
                        ->select('id')
                        ->where('hash', $invite)
                        ->where('used', 0)
                        ->first();

                    $validator->notEmpty($invitation, ['invite' => 'Ключ приглашения недействителен!']);
                }

                // Регистрация аккаунта
                if ($validator->isValid()) {
                    if (setting('regkeys')) {
                        $activateKey  = Str::random();
                        $activateLink = siteUrl(true).'/key?code=' . $activateKey;
                        $level        = User::PENDED;
                    }

                    /* @var User $user */
                    $user = User::query()->create([
                        'login'         => $login,
                        'password'      => password_hash($password, PASSWORD_BCRYPT),
                        'email'         => $email,
                        'level'         => $level,
                        'gender'        => $gender,
                        'themes'        => 0,
                        'point'         => 0,
                        'language'      => setting('language'),
                        'money'         => setting('registermoney'),
                        'confirmregkey' => $activateKey,
                        'subscribe'     => Str::random(32),
                        'updated_at'    => SITETIME,
                        'created_at'    => SITETIME,
                    ]);

                    // Активация пригласительного ключа
                    if ($invitation && setting('invite')) {
                        $invitation->update([
                            'used'           => 1,
                            'invite_user_id' => $user->id,
                        ]);
                    }

                    // ----- Уведомление в приват ----//
                    $textNotice = textNotice('register', ['%username%' => $login]);
                    $user->sendMessage(null, $textNotice);

                    // --- Уведомление о регистрации на email ---//
                    $message = 'Добро пожаловать, ' . $login . '<br>Теперь вы зарегистрированный пользователь сайта <a href="' . siteUrl(true) . '">' . setting('title') . '</a> , сохраните ваш пароль и логин в надежном месте, они вам еще пригодятся. <br>Ваши данные для входа на сайт <br><b>Логин: ' . $login . '</b><br><b>Пароль: ' . $password . '</b><br><br>';

                    $subject = 'Регистрация на сайте ' . setting('title');
                    $body = view('mailer.register', compact('subject', 'message', 'activateKey', 'activateLink'));
                    sendMail($email, $subject, $body);

                    User::auth($login, $password);

                    setFlash('success', 'Добро пожаловать, ' . $login . '!');
                    redirect('/');

                } else {
                    setInput($request->all());
                    setFlash('danger', $validator->getErrors());
                }
            }

            if ($request->has('token')) {
                User::socialAuth($request->input('token'));
            }
        }

        return view('users/register');
    }

    /**
     * Авторизация
     *
     * @param Request $request
     *
     * @return string
     * @throws ErrorException
     */
    public function login(Request $request): string
    {
        if (getUser()) {
            abort(403, 'Вы уже авторизованы!');
        }

        $cooklog = isset($_COOKIE['login']) ? check($_COOKIE['login']): '';

        if ($request->isMethod('post')) {
            if ($request->has('login') && $request->has('pass')) {
               if (captchaVerify()) {
                   $return   = $request->input('return');
                   $login    = check(utfLower($request->input('login')));
                   $pass     = trim($request->input('pass'));
                   $remember = $request->input('remember');

                   /** @var User $user */
                   if ($user = User::auth($login, $pass, $remember)) {
                       setFlash('success', 'Добро пожаловать, ' . $user->login . '!');
                       redirect($return ?? '/');
                   }

                   setInput($request->all());
                   setFlash('danger', 'Неправильный логин или пароль!');
               } else {
                   setInput($request->all());
                   setFlash('danger', __('validator.captcha'));
               }
            }

            if ($request->has('token')) {
                User::socialAuth($request->input('token'));
            }
        }

        return view('users/login', compact('cooklog'));
    }

    /**
     * Выход
     *
     * @param Request $request
     *
     * @return void
     */
    public function logout(Request $request): void
    {
        $token  = check($request->input('token'));
        $domain = siteDomain(siteUrl());

        if ($token === $_SESSION['token']) {
            $_SESSION = [];
            setcookie('password', '', strtotime('-1 hour', SITETIME), '/', $domain, false, true);
            setcookie(session_name(), '', strtotime('-1 hour', SITETIME), '/', '');
            session_destroy();
        } else {
            setFlash('danger', __('validator.token'));
        }

        redirect('/');
    }

    /**
     * Редактирование профиля
     *
     * @param Request   $request
     * @param Validator $validator
     *
     * @return string
     */
    public function profile(Request $request, Validator $validator): string
    {
        if (! $user = getUser()) {
            abort(403, 'Авторизуйтесь для изменения данных в профиле!');
        }

        if ($request->isMethod('post')) {

            $token    = check($request->input('token'));
            $info     = check($request->input('info'));
            $name     = check($request->input('name'));
            $country  = check($request->input('country'));
            $city     = check($request->input('city'));
            $phone    = preg_replace('/\D/', '', $request->input('phone'));
            $site     = check($request->input('site'));
            $birthday = check($request->input('birthday'));
            $gender   = $request->input('gender') === 'male' ? 'male' : 'female';

            $validator->equal($token, $_SESSION['token'], __('validator.token'))
                ->regex($site, '#^https?://([а-яa-z0-9_\-\.])+(\.([а-яa-z0-9\/])+)+$#u', ['site' => __('validator.site')], false)
                ->regex($birthday, '#^[0-9]{2}+\.[0-9]{2}+\.[0-9]{4}$#', ['birthday' => __('validator.date')], false)
                ->regex($phone, '#^\d{11}$#', ['phone' => __('validator.phone')], false)
                ->length($info, 0, 1000, ['info' => 'Слишком большая информация о себе!'])
                ->length($name, 3, 20, ['name' => 'Слишком длинное или короткое имя!'], false);

            if ($validator->isValid()) {
                $country = utfSubstr($country, 0, 30);
                $city    = utfSubstr($city, 0, 50);

                $user->update([
                    'name'     => $name,
                    'gender'   => $gender,
                    'country'  => $country,
                    'city'     => $city,
                    'phone'    => $phone,
                    'site'     => $site,
                    'birthday' => $birthday,
                    'info'     => $info,
                ]);

                setFlash('success', 'Ваш профиль успешно изменен!');
                redirect('/profile');

            } else {
                setInput($request->all());
                setFlash('danger', $validator->getErrors());
            }
        }

        return view('users/profile', compact('user'));
    }

    /**
     * Подтверждение регистрации
     *
     * @param Request   $request
     * @param Validator $validator
     *
     * @return string
     */
    public function key(Request $request, Validator $validator): string
    {
        /* @var User $user */
        if (! $user = getUser()) {
            abort(403, 'Для подтверждения регистрации  необходимо быть авторизованным!');
        }

        if (! setting('regkeys')) {
            abort('default', 'Подтверждение регистрации выключено на сайте!');
        }

        if ($user->level !== User::PENDED) {
            abort(403, 'Вашему профилю не требуется подтверждение регистрации!');
        }

        /* Повторная отправка */
        if ($request->has('email') && $request->isMethod('post')) {

            $token  = check($request->input('token'));
            $email  = strtolower(check($request->input('email')));
            $domain = utfSubstr(strrchr($email, '@'), 1);

            $validator->equal($token, $_SESSION['token'], __('validator.token'))
                ->true(captchaVerify(), ['protect' => __('validator.captcha')])
                ->email($email, ['email' => 'Вы ввели неверный адрес email, необходим формат name@site.domen!']);

            $regMail = User::query()->where('login', '<>', $user->login)->where('email', $email)->count();
            $validator->empty($regMail, ['email' => 'Указанный вами адрес email уже используется в системе!']);

            $blackMail = BlackList::query()->where('type', 'email')->where('value', $email)->count();
            $validator->empty($blackMail, ['email' => 'Указанный вами адрес email занесен в черный список!']);

            $blackDomain = Blacklist::query()->where('type', 'domain')->where('value', $domain)->count();
            $validator->empty($blackDomain, ['email' => 'Домен от вашего адреса email занесен в черный список!']);

            if ($validator->isValid()) {
                $activateKey  = Str::random();
                $activateLink = siteUrl(true).'/key?code=' . $activateKey;

                $user->update([
                    'email'         => $email,
                    'confirmregkey' => $activateKey,
                ]);

                /* Уведомление о регистрации на email */
                $message = 'Добро пожаловать, ' . $user->login . '<br>Теперь вы зарегистрированный пользователь сайта <a href="' . siteUrl(true) . '">' . setting('title') . '</a> , сохраните ваш пароль и логин в надежном месте, они вам еще пригодятся. <br><br>';

                $subject = 'Регистрация на сайте ' . setting('title');
                $body = view('mailer.register', compact('subject', 'message', 'activateKey', 'activateLink'));

                sendMail($email, $subject, $body);

                setFlash('success', 'Новый код подтверждения успешно отправлен!');
                redirect('/');
            } else {
                setInput($request->all());
                setFlash('danger', $validator->getErrors());
            }
        }

        /* Подтверждение кода */
        if ($request->has('code')) {
            $code = check(trim($request->input('code')));

            if ($code === $user->confirmregkey) {

                $user->update([
                    'confirmregkey' => null,
                    'level'         => User::USER,
                ]);

                setFlash('success', 'Аккаунт успешно активирован!');
                redirect('/');

            } else {
                setFlash('danger', 'Ключ не совпадает с данными, проверьте правильность ввода');
            }
        }

        return view('users/key', compact('user'));
    }

    /**
     * Настройки
     *
     * @param Request   $request
     * @param Validator $validator
     *
     * @return string
     */
    public function setting(Request $request, Validator $validator): string
    {
        if (! $user = getUser()) {
            abort(403, __('main.not_authorized'));
        }

        $setting['themes']    = array_map('basename', glob(HOME . '/themes/*', GLOB_ONLYDIR));
        $setting['languages'] = array_map('basename', glob(RESOURCES . '/lang/*', GLOB_ONLYDIR));
        $setting['timezones'] = range(-12, 12);

        if ($request->isMethod('post')) {
            $token     = check($request->input('token'));
            $themes    = check($request->input('themes'));
            $timezone  = check($request->input('timezone', 0));
            $language  = check($request->input('language'));
            $notify    = $request->input('notify') ? 1 : 0;
            $subscribe = $request->input('subscribe') ? Str::random(32) : null;

            $validator->equal($token, $_SESSION['token'], __('validator.token'))
                ->regex($themes, '|^[a-z0-9_\-]+$|i', ['themes' => 'Недопустимое название темы!'])
                ->true(in_array($themes, $setting['themes'], true) || empty($themes), ['themes' => 'Данная тема не установлена на сайте!'])
                ->regex($language, '|^[a-z]+$|', ['language' => 'Недопустимое название языка!'])
                ->in($language, $setting['languages'], ['language' => 'Данный язык не установлен на сайте!'])
                ->regex($timezone, '|^[\-\+]{0,1}[0-9]{1,2}$|', ['timezone' => 'Недопустимое значение временного сдвига. (Допустимый диапазон -12 — +12 часов)!']);

            if ($validator->isValid()) {
                $user->update([
                    'themes'    => $themes,
                    'timezone'  => $timezone,
                    'notify'    => $notify,
                    'subscribe' => $subscribe,
                    'language'  => $language,
                ]);

                setFlash('success', 'Настройки успешно изменены!');
                redirect('/settings');
            } else {
                setInput($request->all());
                setFlash('danger', $validator->getErrors());
            }
        }

        return view('users/settings', compact('user', 'setting'));
    }

    /**
     * Данные пользователя
     *
     * @return string
     */
    public function account(): string
    {
        if (! $user = getUser()) {
            abort(403, __('main.not_authorized'));
        }

        return view('users/account', compact('user'));
    }

    /**
     * Инициализация изменения email
     *
     * @param Request   $request
     * @param Validator $validator
     *
     * @return void
     */
    public function changeMail(Request $request, Validator $validator): void
    {
        if (! $user = getUser()) {
            abort(403, __('main.not_authorized'));
        }

        $token    = check($request->input('token'));
        $email    = check(strtolower($request->input('email')));
        $password = check($request->input('password'));

        $validator->equal($token, $_SESSION['token'], __('validator.token'))
            ->notEqual($email, $user->email, ['email' => 'Новый адрес email должен отличаться от текущего!'])
            ->email($email, ['email' => 'Неправильный адрес email, необходим формат name@site.domen!'])
            ->true(password_verify($password, $user->password), ['password' => 'Введенный пароль не совпадает с данными в профиле!']);

        $regMail = User::query()->where('email', $email)->first();
        $validator->empty($regMail, ['email' => 'Указанный вами адрес email уже используется в системе!']);

        // Проверка email в черном списке
        $blackMail = BlackList::query()->where('type', 'email')->where('value', $email)->first();
        $validator->empty($blackMail, ['email' => 'Указанный вами адрес email занесен в черный список!']);

        ChangeMail::query()->where('created_at', '<', SITETIME)->delete();

        $changeMail = ChangeMail::query()->where('user_id', $user->id)->first();
        $validator->empty($changeMail, 'Вы уже отправили код подтверждения на новый адрес почты!');

        if ($validator->isValid()) {
            $genkey = Str::random(mt_rand(15,20));

            $subject = 'Изменение email на сайте '.setting('title');
            $message = 'Здравствуйте, '.$user->login.'<br>Вами была произведена операция по изменению адреса электронной почты<br><br>Для того, чтобы изменить email, необходимо подтвердить новый адрес почты<br>Перейдите по данной ссылке:<br><br><a href="'.siteUrl(true).'/accounts/editmail?key='.$genkey.'">'.siteUrl(true).'/accounts/editmail?key='.$genkey.'</a><br><br>Ссылка будет дейстительной в течение суток до '.date('j.m.y / H:i', strtotime('+1 day', SITETIME)).'<br>Для изменения адреса необходимо быть авторизованным на сайте<br>Если это сообщение попало к вам по ошибке или вы не собираетесь менять email, то просто проигнорируйте данное письмо';

            $body = view('mailer.default', compact('subject', 'message'));
            sendMail($email, $subject, $body);

            changeMail::query()->create([
                'user_id'    => $user->id,
                'mail'       => $email,
                'hash'       => $genkey,
                'created_at' => strtotime('+1 day', SITETIME),
            ]);

            setFlash('success', 'На новый адрес почты отправлено письмо для подтверждения!');
        } else {
            setInput($request->all());
            setFlash('danger', $validator->getErrors());
        }

        redirect('/accounts');
    }

    /**
     * Изменение email
     *
     * @param Request   $request
     * @param Validator $validator
     *
     * @return void
     * @throws Exception
     */
    public function editMail(Request $request, Validator $validator): void
    {
        if (! $user = getUser()) {
            abort(403, __('main.not_authorized'));
        }

        $key = check($request->input('key'));

        ChangeMail::query()->where('created_at', '<', SITETIME)->delete();

        $changeMail = ChangeMail::query()->where('hash', $key)->where('user_id', $user->id)->first();

        $validator->notEmpty($key, 'Вы не ввели код изменения электронной почты!')
            ->notEmpty($changeMail, 'Данный код изменения электронной почты не найден в списке!');

        if ($changeMail) {
            $validator->notEqual($changeMail->mail, $user->mail, 'Новый адрес email должен отличаться от текущего!');

            $regMail = User::query()->where('email', $changeMail->mail)->count();
            $validator->empty($regMail, 'Указанный вами адрес email уже используется в системе!');

            $blackMail = BlackList::query()->where('type', 'email')->where('value', $changeMail->mail)->count();
            $validator->empty($blackMail, 'Указанный вами адрес email занесен в черный список!');
        }

        if ($validator->isValid()) {
            $user->update([
                'email' => $changeMail->mail,
            ]);

            $changeMail->delete();

            setFlash('success', 'Адрес электронной почты успешно изменен!');
        } else {
            setFlash('danger', $validator->getErrors());
        }

        redirect('/accounts');
    }

    /**
     * Изменение статуса
     *
     * @param Request   $request
     * @param Validator $validator
     *
     * @return void
     */
    public function editStatus(Request $request, Validator $validator): void
    {
        if (! $user = getUser()) {
            abort(403, __('main.not_authorized'));
        }

        $token  = check($request->input('token'));
        $status = check($request->input('status'));
        $cost   = $status ? setting('editstatusmoney') : 0;

        $validator->equal($token, $_SESSION['token'], __('validator.token'))
            ->empty($user->ban, ['status' => 'Для изменения статуса у вас не должно быть нарушений!'])
            ->notEqual($status, $user->status, ['status' => 'Новый статус должен отличаться от текущего!'])
            ->gte($user->point, setting('editstatuspoint'), ['status' => 'У вас недостаточно актива для изменения статуса!'])
            ->gte($user->money, setting('editstatusmoney'), ['status' => 'У вас недостаточно денег для изменения статуса!'])
            ->length($status, 3, 20, ['status' => 'Слишком длинный или короткий статус!'], false);

        if ($status) {
            $checkStatus = User::query()->where('status', $status)->count();
            $validator->empty($checkStatus, ['status' => 'Выбранный вами статус уже используется на сайте!']);
        }

        if ($validator->isValid()) {
            $user->update([
                'status' => $status,
                'money'  => DB::connection()->raw('money - ' . $cost),
            ]);
            $user->saveStatus();

            setFlash('success', 'Ваш статус успешно изменен!');
        } else {
            setInput($request->all());
            setFlash('danger', $validator->getErrors());
        }

        redirect('/accounts');
    }

    /**
     * Изменение пароля
     *
     * @param Request   $request
     * @param Validator $validator
     *
     * @return void
     */
    public function editPassword(Request $request, Validator $validator): void
    {
        if (! $user = getUser()) {
            abort(403, __('main.not_authorized'));
        }

        $token    = check($request->input('token'));
        $newpass  = check($request->input('newpass'));
        $newpass2 = check($request->input('newpass2'));
        $oldpass  = check($request->input('oldpass'));

        $validator->equal($token, $_SESSION['token'], __('validator.token'))
            ->true(password_verify($oldpass, $user->password), ['oldpass' => 'Введенный пароль не совпадает с данными в профиле!'])
            ->length($newpass, 6, 20, ['newpass' => 'Слишком длинный или короткий новый пароль!'])
            ->notEqual(getUser('login'), $newpass, ['newpass' => 'Пароль и логин должны отличаться друг от друга!'])
            ->equal($newpass, $newpass2, ['newpass2' => 'Новые пароли не одинаковые!']);

        if (ctype_digit($newpass)) {
            $validator->addError(['newpass' => 'Запрещен пароль состоящий только из цифр, используйте буквы!']);
        }

        if ($validator->isValid()) {
            $user->update([
                'password' => password_hash($newpass, PASSWORD_BCRYPT),
            ]);

            $subject = 'Изменение пароля на сайте ' . setting('title');
            $message = 'Здравствуйте, ' . getUser('login') . '<br>Вами была произведена операция по изменению пароля<br><br><b>Ваш новый пароль: ' . $newpass . '</b><br>Сохраните его в надежном месте<br><br>Данные инициализации:<br>IP: ' . getIp() . '<br>Браузер: ' . getBrowser() . '<br>Время: ' . date('j.m.y / H:i', SITETIME);

            $body = view('mailer.default', compact('subject', 'message'));
            sendMail($user->email, $subject, $body);

            unset($_SESSION['id'], $_SESSION['password']);

            setFlash('success', 'Пароль успешно изменен!');
            redirect('/login');
        } else {
            setInput($request->all());
            setFlash('danger', $validator->getErrors());
            redirect('/accounts');
        }
    }

    /**
     * Генерация ключа
     *
     * @param Request $request
     *
     * @return void
     */
    public function apikey(Request $request): void
    {
        if (! $user = getUser()) {
            abort(403, __('main.not_authorized'));
        }

        $token = check($request->input('token'));

        if ($token === $_SESSION['token']) {

            $user->update([
                'apikey' => md5(getUser('login') . Str::random()),
            ]);

            setFlash('success', 'Новый ключ успешно сгенерирован!');
        } else {
            setFlash('danger', __('validator.token'));
        }

        redirect('/accounts');
    }

    /**
     * Пользователи онлайн
     *
     * @return string
     */
    public function who(): string
    {
        $online = Online::query()
            ->whereNotNull('user_id')
            ->with('user')
            ->get();

        $birthdays = User::query()
            ->whereRaw('substr(birthday, 1, 5) = ?', date('d.m', SITETIME))
            ->get();

        $novices = User::query()
            ->where('created_at', '>', strtotime('-1 day', SITETIME))
            ->get();

        return view('users/who', compact('online', 'birthdays', 'novices'));
    }
}
