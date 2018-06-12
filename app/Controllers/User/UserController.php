<?php

namespace App\Controllers\User;

use App\Classes\Request;
use App\Classes\Validator;
use App\Controllers\BaseController;
use App\Models\BlackList;
use App\Models\ChangeMail;
use App\Models\Invite;
use App\Models\Online;
use App\Models\User;
use Illuminate\Database\Capsule\Manager as DB;

class UserController extends BaseController
{
    /**
     * Анкета пользователя
     */
    public function index($login)
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
     */
    public function note($login)
    {
        if (! isAdmin()) {
            abort(403, 'Данная страница доступна только администрации!');
        }

        if (! $user = getUserByLogin($login)) {
            abort(404, 'Пользователя с данным логином не существует!');
        }

        if (Request::isMethod('post')) {

            $notice = check(Request::input('notice'));
            $token  = check(Request::input('token'));

            $validator = new Validator();
            $validator->equal($token, $_SESSION['token'], ['notice' => 'Неверный идентификатор сессии, повторите действие!'])
                ->length($notice, 0, 1000, ['notice' => 'Слишком большая заметка, не более 1000 символов!']);

            if ($validator->isValid()) {

                $user->note()->updateOrCreate([], [
                    'text'         => $notice,
                    'edit_user_id' => getUser('id'),
                    'updated_at'   => SITETIME,
                ]);

                setFlash('success', 'Заметка успешно сохранена!');
                redirect('/users/'.$user->login);

            } else {
                setInput(Request::all());
                setFlash('danger', $validator->getErrors());
            }
        }

        return view('users/note', compact('user'));
    }

    /**
     * Регистрация
     */
    public function register()
    {
        if (getUser()) {
            abort('403', 'Вы уже регистрировались, запрещено создавать несколько аккаунтов!');
        }

        if (! setting('openreg')) {
            abort('default', 'Регистрация временно приостановлена, пожалуйста зайдите позже!');
        }

        if (Request::isMethod('post')) {
            if (Request::has('login') && Request::has('password')) {
                $login       = check(Request::input('login'));
                $password    = trim(Request::input('password'));
                $password2   = trim(Request::input('password2'));
                $invite      = setting('invite') ? check(Request::input('invite')) : '';
                $email       = strtolower(check(Request::input('email')));
                $domain      = utfSubstr(strrchr($email, '@'), 1);
                $gender      = Request::input('gender') == 'male' ? 'male' : 'female';
                $activateKey = null;
                $level       = User::USER;

                $validator = new Validator();
                $validator->true(captchaVerify(), ['protect' => 'Не удалось пройти проверку captcha!'])
                    ->regex($login, '|^[a-z0-9\-]+$|i', ['login' => 'Недопустимые символы в логине. Разрешены знаки латинского алфавита, цифры и дефис!'])
                    ->regex(utfSubstr($login, 0, 1), '|^[a-z0-9]+$|i', ['login' => 'Логин должен начинаться с буквы или цифры!'])
                    ->email($email, ['email' => 'Вы ввели неверный адрес email, необходим формат name@site.domen!'])
                    ->length($invite, 12, 15, ['invite' => 'Слишком длинный или короткий пригласительный ключ!'], setting('invite'))
                    ->length($login, 3, 20, ['login' => 'Слишком длинный или короткий логин!'])
                    ->length($password, 6, 20, ['password' => 'Слишком длинный или короткий пароль!'])
                    ->equal($password, $password2, ['password2' => 'Ошибка! Введенные пароли отличаются друг от друга!']);

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
                        ->where('type', 2)
                        ->where('value', strtolower($login))
                        ->count();
                    $validator->empty($blackLogin, ['login' => 'Выбранный вами логин занесен в черный список!']);
                }

                // Проверка email на существование
                $checkMail = User::query()->where('email', $email)->count();
                $validator->empty($checkMail, ['email' => 'Указанный вами адрес email уже используется в системе!']);

                // Проверка домена от email в черном списке
                $blackDomain = Blacklist::query()
                    ->where('type', 3)
                    ->where('value', $domain)
                    ->count();
                $validator->empty($blackDomain, ['email' => 'Домен от вашего адреса email занесен в черный список!']);

                // Проверка email в черном списке
                $blackMail = Blacklist::query()
                    ->where('type', 1)
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

                    // --- Уведомление о регистрации на email ---//
                    $message = 'Добро пожаловать, ' . $login . '<br>Теперь вы зарегистрированный пользователь сайта <a href="' . siteUrl(true) . '">' . setting('title') . '</a> , сохраните ваш пароль и логин в надежном месте, они вам еще пригодятся. <br>Ваши данные для входа на сайт <br><b>Логин: ' . $login . '</b><br><b>Пароль: ' . $password . '</b><br><br>';

                    if (setting('regkeys')) {
                        $activateKey  = str_random();
                        $activateLink = siteUrl(true).'/key?code=' . $activateKey;
                        $level        = User::PENDED;
                    }

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
                        'subscribe'     => str_random(32),
                        'updated_at'    => SITETIME,
                        'created_at'    => SITETIME,
                    ]);

                    // Активация пригласительного ключа
                    if (setting('invite')) {
                        $invitation->update([
                            'used'           => 1,
                            'invite_user_id' => $user->id,
                        ]);
                    }

                    // ----- Уведомление в приват ----//
                    $textNotice = textNotice('register', ['%username%' => $login]);
                    $user->sendMessage(null, $textNotice);

                    $subject = 'Регистрация на сайте ' . setting('title');
                    $body = view('mailer.register', compact('subject', 'message', 'activateKey', 'activateLink'));
                    sendMail($email, $subject, $body);

                    User::auth($login, $password);

                    setFlash('success', 'Добро пожаловать, ' . $login . '!');
                    redirect('/');

                } else {
                    setInput(Request::all());
                    setFlash('danger', $validator->getErrors());
                }
            }

            if (Request::has('token')) {
                User::socialAuth(Request::input('token'));
            }
        }

        return view('users/registration');
    }

    /**
     * Авторизация
     */
    public function login()
    {
        if (getUser()) {
            abort('403', 'Вы уже авторизованы!');
        }

        $cooklog = isset($_COOKIE['login']) ? check($_COOKIE['login']): '';
        if (Request::isMethod('post')) {
            if (Request::has('login') && Request::has('pass')) {
                $return   = Request::input('return', '');
                $login    = check(utfLower(Request::input('login')));
                $pass     = trim(Request::input('pass'));
                $remember = Request::input('remember');

                if ($user = User::auth($login, $pass, $remember)) {
                    setFlash('success', 'Добро пожаловать, '.$user->login.'!');

                    if ($return) {
                        redirect($return);
                    } else {
                        redirect('/');
                    }
                }

                setInput(Request::all());
                setFlash('danger', 'Ошибка авторизации. Неправильный логин или пароль!');
            }

            if (Request::has('token')) {
                User::socialAuth(Request::input('token'));
            }
        }

        return view('users/login', compact('cooklog'));
    }

    /**
     * Выход
     */
    public function logout()
    {
        $token  = check(Request::input('token'));
        $domain = siteDomain(siteUrl());

        if ($token === $_SESSION['token']) {
            $_SESSION = [];
            setcookie('password', '', SITETIME - 3600, '/', $domain, null, true);
            setcookie(session_name(), '', SITETIME - 3600, '/', '');
            session_destroy();
        } else {
            setFlash('danger', 'Ошибка! Неверный идентификатор сессии, повторите действие!');
        }

        redirect('/');
    }

    /**
     * Редактирование профиля
     */
    public function profile()
    {
        if (! $user = getUser()) {
            abort(403, 'Авторизуйтесь для изменения данных в профиле!');
        }

        if (Request::isMethod('post')) {

            $token    = check(Request::input('token'));
            $info     = check(Request::input('info'));
            $name     = check(Request::input('name'));
            $country  = check(Request::input('country'));
            $city     = check(Request::input('city'));
            $icq      = check(str_replace('-', '', Request::input('icq')));
            $skype    = check(strtolower(Request::input('skype')));
            $site     = check(Request::input('site'));
            $birthday = check(Request::input('birthday'));
            $gender   = Request::input('gender') === 'male' ? 'male' : 'female';

            $validator = new Validator();
            $validator->equal($token, $_SESSION['token'], 'Неверный идентификатор сессии, повторите действие!')
                ->regex($site, '#^https?://([а-яa-z0-9_\-\.])+(\.([а-яa-z0-9\/])+)+$#u', ['site' => 'Недопустимый адрес сайта, необходим формата http://my_site.domen!'], false)
                ->regex($birthday, '#^[0-9]{2}+\.[0-9]{2}+\.[0-9]{4}$#', ['birthday' => 'Недопустимый формат даты рождения, необходим формат дд.мм.гггг!'], false)
                ->regex($icq, '#^[0-9]{5,10}$#', ['icq' => 'Недопустимый формат ICQ, только цифры от 5 до 10 символов!'], false)
                ->regex($skype, '#^[a-z]{1}[0-9a-z\_\.\-]{5,31}$#', ['skype' => 'Недопустимый формат Skype, только латинские символы от 6 до 32!'], false)
                ->length($info, 0, 1000, ['info' => 'Слишком большая информация о себе, не более 1000 символов!'])
                ->length($name, 3, 20, ['name' => 'Слишком длинное или короткое имя!'], false);

            if ($validator->isValid()) {

                $country = utfSubstr($country, 0, 30);
                $city    = utfSubstr($city, 0, 50);

                $user->update([
                    'name'     => $name,
                    'gender'   => $gender,
                    'country'  => $country,
                    'city'     => $city,
                    'icq'      => $icq,
                    'skype'    => $skype,
                    'site'     => $site,
                    'birthday' => $birthday,
                    'info'     => $info,
                ]);

                setFlash('success', 'Ваш профиль успешно изменен!');
                redirect('/profile');

            } else {
                setInput(Request::all());
                setFlash('danger', $validator->getErrors());
            }
        }

        return view('users/profile', compact('user'));
    }

    /*
     * Подтверждение регистрации
     */
    function key()
    {
        if (! $user = getUser()) {
            abort(403, 'Для подтверждения регистрации  необходимо быть авторизованным!');
        }

        if (! setting('regkeys')) {
            abort('default', 'Подтверждение регистрации выключено на сайте!');
        }

        if ($user->level != User::PENDED) {
            abort(403, 'Вашему профилю не требуется подтверждение регистрации!');
        }

        if (Request::has('code')) {
            $code = check(trim(Request::input('code')));

            if ($code == $user->confirmregkey) {

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

        return view('users/key');
    }

    /*
     * Настройки
     */
    function setting()
    {
        if (! $user = getUser()) {
            abort(403, 'Для изменения настроек необходимо авторизоваться!');
        }

        $setting['themes']    = array_map('basename', glob(HOME."/themes/*", GLOB_ONLYDIR));
        $setting['languages'] = array_map('basename', glob(RESOURCES."/lang/*", GLOB_ONLYDIR));
        $setting['timezones'] = range(-12, 12);

        if (Request::isMethod('post')) {

            $token     = check(Request::input('token'));
            $themes    = check(Request::input('themes'));
            $timezone  = check(Request::input('timezone', 0));
            $language  = check(Request::input('language'));
            $notify    = Request::input('notify') == 1 ? 1 : 0;
            $subscribe = Request::input('subscribe') == 1 ? str_random(32) : null;

            $validator = new Validator();
            $validator->equal($token, $_SESSION['token'], 'Неверный идентификатор сессии, повторите действие!')
                ->regex($themes, '|^[a-z0-9_\-]+$|i', ['themes' => 'Недопустимое название темы!'])
                ->true(in_array($themes, $setting['themes']) || empty($themes), ['themes' => 'Данная тема не установлена на сайте!'])
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
                setInput(Request::all());
                setFlash('danger', $validator->getErrors());
            }
        }

        return view('users/settings', compact('user', 'setting'));
    }

    /**
     * Данные пользователя
     */
    public function account()
    {
        if (! $user = getUser()) {
            abort(403, 'Для изменения данных необходимо авторизоваться!');
        }

        return view('users/account', compact('user'));
    }

    /**
     * Инициализация изменения email
     */
    public function changeMail()
    {
        if (! $user = getUser()) {
            abort(403, 'Для изменения данных необходимо авторизоваться!');
        }

        $token    = check(Request::input('token'));
        $email    = check(strtolower(Request::input('email')));
        $password = check(Request::input('password'));

        $validator = new Validator();
        $validator->equal($token, $_SESSION['token'], 'Неверный идентификатор сессии, повторите действие!')
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

            $genkey = str_random(random_int(15,20));

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
            setInput(Request::all());
            setFlash('danger', $validator->getErrors());
        }

        redirect('/accounts');
    }

    /**
     * Изменение email
     */
    public function editMail()
    {
        if (! $user = getUser()) {
            abort(403, 'Для изменения данных необходимо авторизоваться!');
        }

        $key = check(Request::input('key'));

        ChangeMail::query()->where('created_at', '<', SITETIME)->delete();

        $changeMail = ChangeMail::query()->where('hash', $key)->where('user_id', $user->id)->first();

        $validator = new Validator();
        $validator->notEmpty($key, 'Вы не ввели код изменения электронной почты!')
            ->notEmpty($changeMail, 'Данный код изменения электронной почты не найден в списке!');

        if ($changeMail) {
            $validator->notEqual($changeMail->mail, $user->mail, 'Новый адрес email должен отличаться от текущего!');

            $regMail = User::query()->where('email', $changeMail->mail)->first();
            $validator->empty($regMail, 'Указанный вами адрес email уже используется в системе!');

            $blackMail = BlackList::query()->where('type', 'email')->where('value', $changeMail->mail)->first();
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
     */
    public function editStatus()
    {
        if (! $user = getUser()) {
            abort(403, 'Для изменения данных необходимо авторизоваться!');
        }

        $token  = check(Request::input('token'));
        $status = check(Request::input('status'));
        $cost   = $status ? setting('editstatusmoney') : 0;

        $validator = new Validator();
        $validator->equal($token, $_SESSION['token'], 'Неверный идентификатор сессии, повторите действие!')
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
                'money'  => DB::raw('money - '.$cost),
            ]);
            $user->saveStatus();

            setFlash('success', 'Ваш статус успешно изменен!');
        } else {
            setInput(Request::all());
            setFlash('danger', $validator->getErrors());
        }

        redirect('/accounts');
    }

    /**
     * Изменение пароля
     */
    public function editPassword()
    {
        if (! $user = getUser()) {
            abort(403, 'Для изменения данных необходимо авторизоваться!');
        }

        $token    = check(Request::input('token'));
        $newpass  = check(Request::input('newpass'));
        $newpass2 = check(Request::input('newpass2'));
        $oldpass  = check(Request::input('oldpass'));

        $validator = new Validator();
        $validator->equal($token, $_SESSION['token'], 'Неверный идентификатор сессии, повторите действие!')
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

            $subject = 'Изменение пароля на сайте '.setting('title');
            $message = 'Здравствуйте, '.getUser('login').'<br>Вами была произведена операция по изменению пароля<br><br><b>Ваш новый пароль: '.$newpass.'</b><br>Сохраните его в надежном месте<br><br>Данные инициализации:<br>IP: '.getIp().'<br>Браузер: '.getBrowser().'<br>Время: '.date('j.m.y / H:i', SITETIME);

            $body = view('mailer.default', compact('subject', 'message'));
            sendMail($user->email, $subject, $body);

            unset($_SESSION['id'], $_SESSION['password']);

            setFlash('success', 'Пароль успешно изменен!');
            redirect('/login');
        } else {
            setInput(Request::all());
            setFlash('danger', $validator->getErrors());
            redirect('/accounts');
        }
    }

    /**
     * Генерация ключа
     */
    public function apikey()
    {
        if (! $user = getUser()) {
            abort(403, 'Для изменения данных необходимо авторизоваться!');
        }

        $token = check(Request::input('token'));

        if ($token == $_SESSION['token']) {

            $user->update([
                'apikey' => md5(getUser('login').str_random()),
            ]);

            setFlash('success', 'Новый ключ успешно сгенерирован!');
        } else {
            setFlash('danger', 'Ошибка! Неверный идентификатор сессии, повторите действие!');
        }

        redirect('/accounts');
    }

    /**
     * Пользователи онлайн
     */
    public function who()
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
