<?php

namespace App\Controllers;

use App\Classes\Request;
use App\Classes\Validation;
use App\Models\BlackList;
use App\Models\ChangeMail;
use App\Models\Invite;
use App\Models\Note;
use App\Models\Rating;
use App\Models\User;
use Illuminate\Database\Capsule\Manager as DB;

class UserController extends BaseController
{
    /**
     * Главная страница
     */
    public function index($login)
    {
        if (! $user = getUserByLogin($login)) {
            abort('default', 'Пользователя с данным логином не существует!');
        }

        $note = Note::query()->where('user_id', $user->id)->first();
        $invite = Invite::query()->where('invite_user_id', $user->id)->first();

        $isAdmin = isAdmin(User::ADMIN);
        $isModer = isAdmin(User::MODER);

        return view('pages/user', compact('user', 'invite', 'note', 'isAdmin', 'isModer'));
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
            abort('default', 'Пользователя с данным логином не существует!');
        }

        $note = Note::query()->where('user_id', $user->id)->first();

        if (Request::isMethod('post')) {

            $notice = check(Request::input('notice'));
            $token  = check(Request::input('token'));

            $validation = new Validation();
            $validation->addRule('equal', [$token, $_SESSION['token']], ['notice' => 'Неверный идентификатор сессии, повторите действие!'])
                ->addRule('string', $notice, ['notice' => 'Слишком большая заметка, не более 1000 символов!'], true, 0, 1000);

            if ($validation->run()) {

                $record = [
                    'user_id'      => $user->id,
                    'text'         => $notice,
                    'edit_user_id' => getUser('id'),
                    'updated_at'   => SITETIME,
                ];

                Note::saveNote($note, $record);

                setFlash('success', 'Заметка успешно сохранена!');
                redirect('/user/'.$user->login);

            } else {
                setInput(Request::all());
                setFlash('danger', $validation->getErrors());
            }
        }

        return view('pages/user_note', compact('note', 'user'));
    }

    /**
     * Изменение рейтинга
     */
    public function rating($login)
    {
        if (! getUser()) {
            abort(403, 'Для изменения рейтинга небходимо авторизоваться!');
        }

        $user = User::query()->where('login', $login)->first();

        if (! $user) {
            abort('default', 'Данного пользователя не существует!');
        }

        if (getUser('id') == $user->id) {
            abort('default', 'Запрещено изменять репутацию самому себе!');
        }

        if (getUser('point') < setting('editratingpoint')) {
            abort('default', 'Для изменения репутации необходимо набрать '.plural(setting('editratingpoint'), setting('scorename')).'!');
        }

        // Голосовать за того же пользователя можно через 90 дней
        $getRating = Rating::query()
            ->where('user_id', getUser('id'))
            ->where('recipient_id', $user->id)
            ->where('created_at', '>', SITETIME - 3600 * 24 * 90)
            ->first();

        if ($getRating) {
            abort('default', 'Вы уже изменяли репутацию этому пользователю!');
        }

        $vote = Request::input('vote') ? 1 : 0;

        if (Request::isMethod('post')) {

            $token = check(Request::input('token'));
            $text  = check(Request::input('text'));

            $validation = new Validation();

            $validation->addRule('equal', [$token, $_SESSION['token']], 'Неверный идентификатор сессии, повторите действие!')
                ->addRule('string', $text, ['text' => 'Слишком длинный или короткий комментарий!'], true, 5, 250);

            if (getUser('rating') < 10 && empty($vote)) {
                $validation->addError('Уменьшать репутацию могут только пользователи с рейтингом 10 или выше!');
            }

            if ($validation->run()) {

                $text = antimat($text);

                Rating::query()->create([
                    'user_id'      => getUser('id'),
                    'recipient_id' => $user->id,
                    'text'         => $text,
                    'vote'         => $vote,
                    'created_at'   => SITETIME,
                ]);

                if ($vote == 1) {
                    $text = 'Пользователь [b]' . getUser('login') . '[/b] поставил вам плюс! (Ваш рейтинг: ' . ($user['rating'] + 1) . ')' . PHP_EOL . 'Комментарий: ' . $text;

                    $user->update([
                        'rating' => DB::raw('posrating - negrating + 1'),
                        'posrating' => DB::raw('posrating + 1'),
                    ]);

                } else {

                    $text = 'Пользователь [b]' . getUser('login') . '[/b] поставил вам минус! (Ваш рейтинг: ' . ($user['rating'] - 1) . ')' . PHP_EOL . 'Комментарий: ' . $text;

                    $user->update([
                        'rating' => DB::raw('posrating - negrating - 1'),
                        'negrating' => DB::raw('negrating + 1'),
                    ]);
                }

                sendPrivate($user->id, getUser('id'), $text);

                setFlash('success', 'Репутация успешно изменена!');
                redirect('/user/'.$user->login);
            } else {
                setInput(Request::all());
                setFlash('danger', $validation->getErrors());
            }
        }

        return view('pages/rating', compact('user', 'vote'));
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
            if (Request::has('logs') && Request::has('pars')) {
                $logs        = check(Request::input('logs'));
                $pars        = trim(Request::input('pars'));
                $pars2       = trim(Request::input('pars2'));
                $protect     = check(strtolower(Request::input('protect')));
                $invite      = setting('invite') ? check(Request::input('invite')) : '';
                $meil        = strtolower(check(Request::input('meil')));
                $domain      = utfSubstr(strrchr($meil, '@'), 1);
                $gender      = Request::input('gender') == 1 ? 1 : 2;
                $activateKey = '';

                $validation = new Validation();
                $validation->addRule('equal', [$protect, $_SESSION['protect']], ['protect' => 'Проверочное число не совпало с данными на картинке!'])
                    ->addRule('regex', [$logs, '|^[a-z0-9\-]+$|i'], ['logs' => 'Недопустимые символы в логине. Разрешены знаки латинского алфавита, цифры и дефис!'], true)
                    ->addRule('regex', [utfSubstr($logs, 0, 1), '|^[a-z0-9]+$|i'], ['logs' => 'Логин должен начинаться с буквы или цифры!'], true)
                    ->addRule('email', $meil, ['meil' => 'Вы ввели неверный адрес email, необходим формат name@site.domen!'], true)
                    ->addRule('string', $invite, ['invite' => 'Слишком длинный или короткий пригласительный ключ!'], setting('invite'), 12, 15)
                    ->addRule('string', $logs, ['logs' => 'Слишком длинный или короткий логин!'], true, 3, 20)
                    ->addRule('string', $pars, ['pars' => 'Слишком длинный или короткий пароль!'], true, 6, 20)
                    ->addRule('equal', [$pars, $pars2], ['pars2' => 'Ошибка! Введенные пароли отличаются друг от друга!']);

                if (ctype_digit($pars)) {
                    $validation->addError(['pars' => 'Запрещен пароль состоящий только из цифр, используйте буквы!']);
                }

                if (substr_count($logs, '-') > 2) {
                    $validation->addError(['logs' => 'Запрещено использовать в логине слишком много дефисов!']);
                }

                if (! empty($logs)) {
                    // Проверка логина на существование
                    $checkLogin = User::query()->whereRaw('lower(login) = ?', [strtolower($logs)])->count();
                    $validation->addRule('empty', $checkLogin, ['logs' => 'Пользователь с данным логином уже зарегистрирован!']);

                    // Проверка логина в черном списке
                    $blackLogin = Blacklist::query()
                        ->where('type', 2)
                        ->where('value', strtolower($logs))
                        ->count();
                    $validation->addRule('empty', $blackLogin, ['logs' => 'Выбранный вами логин занесен в черный список!']);
                }

                // Проверка email на существование
                $checkMail = User::query()->where('email', $meil)->count();
                $validation->addRule('empty', $checkMail, ['meil' => 'Указанный вами адрес email уже используется в системе!']);

                // Проверка домена от email в черном списке
                $blackDomain = Blacklist::query()
                    ->where('type', 3)
                    ->where('value', $domain)
                    ->count();
                $validation->addRule('empty', $blackDomain, ['meil' => 'Домен от вашего адреса email занесен в черный список!']);

                // Проверка email в черном списке
                $blackMail = Blacklist::query()
                    ->where('type', 1)
                    ->where('value', $meil)
                    ->count();
                $validation->addRule('empty', $blackMail, ['meil' => 'Указанный вами адрес email занесен в черный список!']);

                // Проверка пригласительного ключа
                if (setting('invite')) {
                    $invitation = Invite::query()
                        ->select('id')
                        ->where('hash', $invite)
                        ->where('used', 0)
                        ->first();

                    $validation->addRule('not_empty', $invitation, ['invite' => 'Ключ приглашения недействителен!']);
                }

                // Регистрация аккаунта
                if ($validation->run()) {

                    // --- Уведомление о регистрации на email ---//
                    $message = 'Добро пожаловать, ' . $logs . '<br>Теперь вы зарегистрированный пользователь сайта <a href="' . siteLink(setting('home')) . '">' . setting('title') . '</a> , сохраните ваш пароль и логин в надежном месте, они вам еще пригодятся. <br>Ваши данные для входа на сайт <br><b>Логин: ' . $logs . '</b><br><b>Пароль: ' . $pars . '</b><br><br>';

                    if (setting('regkeys') == 1) {
                        $activateKey = str_random();
                        $activateLink = siteLink(setting('home')).'/key?code=' . $activateKey;

                        echo '<b><span style="color:#ff0000">Внимание! После входа на сайт, вам будет необходимо ввести мастер-ключ для подтверждения регистрации<br>';
                        echo 'Мастер-ключ был выслан вам на почтовый ящик: ' . $meil . '</span></b><br><br>';
                    }

                    if (setting('regkeys') == 2) {
                        echo '<b><span style="color:#ff0000">Внимание! Ваш аккаунт будет активирован только после проверки администрацией!</span></b><br><br>';
                    }

                    $user = User::query()->create([
                        'login' => $logs,
                        'password' => password_hash($pars, PASSWORD_BCRYPT),
                        'email' => $meil,
                        'joined' => SITETIME,
                        'level' => 107,
                        'gender' => $gender,
                        'themes' => 0,
                        'point' => 0,
                        'money' => setting('registermoney'),
                        'timelastlogin' => SITETIME,
                        'confirmreg' => setting('regkeys'),
                        'confirmregkey' => $activateKey,
                        'subscribe' => str_random(32),
                    ]);

                    // Активация пригласительного ключа
                    if (setting('invite')) {
                        $invitation->update([
                            'used'           => 1,
                            'invite_user_id' => $user->id,
                        ]);
                    }

                    // ----- Уведомление в приват ----//
                    $textPrivate = textPrivate(1, ['%USERNAME%' => $logs, '%SITENAME%' => setting('home')]);
                    sendPrivate($user->id, 0, $textPrivate);

                    $subject = 'Регистрация на сайте ' . setting('title');
                    $body = view('mailer.register', compact('subject', 'message', 'activateKey', 'activateLink'), true);
                    sendMail($meil, $subject, $body);

                    User::auth($logs, $pars);

                    setFlash('success', 'Добро пожаловать, ' . $logs . '!');
                    redirect('/');

                } else {
                    setInput(Request::all());
                    setFlash('danger', $validation->getErrors());
                }
            }

            if (Request::has('token')) {
                User::socialAuth(Request::input('token'));
            }
        }

        return view('pages/registration');
    }

    /**
     * Авторизация
     */
    public function login()
    {
        if (getUser()) {
            abort('403', 'Вы уже авторизованы!');
        }

        $cooklog = (isset($_COOKIE['login'])) ? check($_COOKIE['login']): '';
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

        return view('pages/login', compact('cooklog'));
    }

    /**
     * Выход
     */
    public function logout()
    {
        $domain = siteDomain(setting('home'));

        $_SESSION = [];
        setcookie('password', '', SITETIME - 3600, '/', $domain, null, true);
        setcookie(session_name(), '', SITETIME - 3600, '/', '');
        session_destroy();

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

            $validation = new Validation();

            $validation->addRule('equal', [$token, $_SESSION['token']], 'Неверный идентификатор сессии, повторите действие!')
                ->addRule('regex', [$site, '#^https?://([а-яa-z0-9_\-\.])+(\.([а-яa-z0-9\/])+)+$#u'], ['site' => 'Недопустимый адрес сайта, необходим формата http://my_site.domen!'], false)
                ->addRule('regex', [$birthday, '#^[0-9]{2}+\.[0-9]{2}+\.[0-9]{4}$#'], ['birthday' => 'Недопустимый формат даты рождения, необходим формат дд.мм.гггг!'], false)
                ->addRule('regex', [$icq, '#^[0-9]{5,10}$#'], ['icq' => 'Недопустимый формат ICQ, только цифры от 5 до 10 символов!'], false)
                ->addRule('regex', [$skype, '#^[a-z]{1}[0-9a-z\_\.\-]{5,31}$#'], ['skype' => 'Недопустимый формат Skype, только латинские символы от 6 до 32!'], false)
                ->addRule('string', $info, ['info' => 'Слишком большая информация о себе, не более 1000 символов!'], true, 0, 1000);

            if ($validation->run()) {

                $name    = utfSubstr($name, 0, 20);
                $country = utfSubstr($country, 0, 30);
                $city    = utfSubstr($city, 0, 50);

                $user->update([
                    'name'     => $name,
                    'country'  => $country,
                    'city'     => $city,
                    'icq'      => $icq,
                    'skype'    => $skype,
                    'site'     => $site,
                    'birthday' => $birthday,
                    'info'     => $info,
                ]);

                setFlash('success', 'Ваш профиль успешно изменен!');
                redirect("/profile");

            } else {
                setInput(Request::all());
                setFlash('danger', $validation->getErrors());
            }
        }

        return view('pages/profile', compact('user'));
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

        if (! $user->confirmreg) {
            abort('default', 'Вашему профилю не требуется подтверждение регистрации!');
        }

        if (Request::has('code')) {
            $code = check(trim(Request::input('code')));

            if ($code == $user->confirmregkey) {

                $user->update([
                    'confirmreg'    => 0,
                    'confirmregkey' => null,
                ]);

                setFlash('success', 'Аккаунт успешно активирован!');
                redirect("/");

            } else {
                setFlash('danger', 'Ключ не совпадает с данными, проверьте правильность ввода');
            }
        }

        view('pages/key');
    }

    /*
     * Настройки
     */
    function setting()
    {
        if (! $user = getUser()) {
            abort(403, 'Для изменения настроек необходимо авторизоваться!');
        }

        if (Request::isMethod('post')) {

            $token     = check(Request::input('token'));
            $themes    = check(Request::input('themes'));
            $timezone  = check(Request::input('timezone', 0));
            $notify    = Request::has('notify') ? 1 : 0;
            $subscribe = Request::has('subscribe') ? str_random(32) : null;

            $validation = new Validation();

            $validation->addRule('equal', [$token, $_SESSION['token']], 'Неверный идентификатор сессии, повторите действие!')
                ->addRule('regex', [$themes, '|^[a-z0-9_\-]+$|i'], 'Недопустимое название темы!', true)
                ->addRule('bool', (file_exists(HOME.'/themes/'.$themes) || empty($themes)), 'Данная тема не установлен на сайте!', true)
                ->addRule('regex', [$timezone, '|^[\-\+]{0,1}[0-9]{1,2}$|'], 'Недопустимое значение временного сдвига. (Допустимый диапазон -12 — +12 часов)!', true);

            if ($validation->run()) {

                $user->update([
                    'themes'    => $themes,
                    'timezone'  => $timezone,
                    'notify'    => $notify,
                    'subscribe' => $subscribe,
                ]);

                setFlash('success', 'Настройки успешно изменены!');
                redirect("/setting");

            } else {
                setFlash('danger', $validation->getErrors());
            }
        }

        $setting['themes']    = glob(HOME."/themes/*", GLOB_ONLYDIR);
        $setting['languages'] = glob(RESOURCES."/lang/*", GLOB_ONLYDIR);

        $setting['langShort'] = [
            'ru' => 'русский',
            'en' => 'English',
        ];

        $setting['timezones'] = range(-12, 12);

        return view('user/setting', compact('user', 'setting'));
    }

    /**
     * Данные пользователя
     */
    public function account()
    {
        if (! $user = getUser()) {
            abort(403, 'Для изменения данных необходимо авторизоваться!');
        }

        return view('user/account', compact('user'));
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
        $meil     = strtolower(check(Request::input('meil')));
        $provpass = check(Request::input('provpass'));

        $validation = new Validation();
        $validation->addRule('equal', [$token, $_SESSION['token']], 'Неверный идентификатор сессии, повторите действие!')
            ->addRule('not_equal', [$meil, $user->email], 'Новый адрес email должен отличаться от текущего!')
            ->addRule('email', $meil, 'Неправильный адрес email, необходим формат name@site.domen!', true)
            ->addRule('bool', password_verify($provpass, $user->password), 'Введенный пароль не совпадает с данными в профиле!');

        $regMail = User::query()->where('email', $meil)->first();
        $validation->addRule('empty', $regMail, 'Указанный вами адрес email уже используется в системе!');

        // Проверка email в черном списке
        $blackMail = BlackList::query()->where('type', 'email')->where('value', $meil)->first();
        $validation->addRule('empty', $blackMail, 'Указанный вами адрес email занесен в черный список!');

        ChangeMail::query()->where('created_at', '<', SITETIME)->delete();

        $changeMail = ChangeMail::query()->where('user_id', $user->id)->first();
        $validation->addRule('empty', $changeMail, 'Вы уже отправили код подтверждения на новый адрес почты!');

        if ($validation->run()) {

            $genkey = str_random(rand(15,20));

            $subject = 'Изменение email на сайте '.setting('title');
            $message = 'Здравствуйте, '.$user->login.'<br>Вами была произведена операция по изменению адреса электронной почты<br><br>Для того, чтобы изменить email, необходимо подтвердить новый адрес почты<br>Перейдите по данной ссылке:<br><br><a href="'.siteLink(setting('home')).'/account/editmail?key='.$genkey.'">'.siteLink(setting('home')).'/account/editmail?key='.$genkey.'</a><br><br>Ссылка будет дейстительной в течение суток до '.date('j.m.y / H:i', SITETIME + 86400).'<br>Для изменения адреса необходимо быть авторизованным на сайте<br>Если это сообщение попало к вам по ошибке или вы не собираетесь менять email, то просто проигнорируйте данное письмо';

            $body = view('mailer.default', compact('subject', 'message'), true);
            sendMail($meil, $subject, $body);

            changeMail::query()->create([
                'user_id'    => $user->id,
                'mail'       => $meil,
                'hash'       => $genkey,
                'created_at' => SITETIME + 86400,
            ]);

            setFlash('success', 'На новый адрес почты отправлено письмо для подтверждения!');
        } else {
            setFlash('danger', $validation->getErrors());
        }

        redirect("/account");
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

        $validation = new Validation();
        $validation->addRule('not_empty', $key, 'Вы не ввели код изменения электронной почты!')
            ->addRule('not_empty', $changeMail, 'Данный код изменения электронной почты не найден в списке!');

        if ($changeMail) {
            $validation->addRule('not_equal', [$changeMail->mail, $user->mail], 'Новый адрес email должен отличаться от текущего!');

            $regMail = User::query()->where('email', $changeMail->mail)->first();
            $validation->addRule('empty', $regMail, 'Указанный вами адрес email уже используется в системе!');

            $blackMail = BlackList::query()->where('type', 'email')->where('value', $changeMail->mail)->first();
            $validation->addRule('empty', $blackMail, 'Указанный вами адрес email занесен в черный список!');
        }

        if ($validation->run()) {

            $user->update([
                'email' => $changeMail->mail,
            ]);

            $changeMail->delete();

            setFlash('success', 'Адрес электронной почты успешно изменен!');
        } else {
            setFlash('danger', $validation->getErrors());
        }

        redirect("/account");
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

        $validation = new Validation();
        $validation->addRule('equal', [$token, $_SESSION['token']], 'Неверный идентификатор сессии, повторите действие!')
            ->addRule('empty', $user->ban, 'Для изменения статуса у вас не должно быть нарушений!')
            ->addRule('not_equal', [$status, $user->status], 'Новый статус должен отличаться от текущего!')
            ->addRule('max', [$user->point, setting('editstatuspoint')], 'У вас недостаточно актива для изменения статуса!')
            ->addRule('max', [$user->money, setting('editstatusmoney')], 'У вас недостаточно денег для изменения статуса!')
            ->addRule('string', $status, 'Слишком длинный или короткий статус!', false, 3, 20);

        if ($status) {
            $checkStatus = User::query()->whereRaw('lower(status) = ?', [utfLower($status)])->count();
            $validation->addRule('empty', $checkStatus, 'Выбранный вами статус уже используется на сайте!');
        }

        if ($validation->run()) {

            $user->update([
                'status' => $status,
                'money'  => DB::raw('money - '.$cost),
            ]);
            saveStatus();

            setFlash('success', 'Ваш статус успешно изменен!');
        } else {
            setFlash('danger', $validation->getErrors());
        }

        redirect("/account");
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

        $validation = new Validation();
        $validation -> addRule('equal', [$token, $_SESSION['token']], 'Неверный идентификатор сессии, повторите действие!')
            ->addRule('bool', password_verify($oldpass, $user->password), 'Введенный пароль не совпадает с данными в профиле!')
            ->addRule('equal', [$newpass, $newpass2], 'Новые пароли не одинаковые!')
            ->addRule('string', $newpass, 'Слишком длинный или короткий новый пароль!', true, 6, 20)
            ->addRule('regex', [$newpass, '|^[a-z0-9\-]+$|i'], 'Недопустимые символы в пароле, разрешены знаки латинского алфавита, цифры и дефис!', true)
            ->addRule('not_equal', [getUser('login'), $newpass], 'Пароль и логин должны отличаться друг от друга!');

        if (ctype_digit($newpass)) {
            $validation->addError('Запрещен пароль состоящий только из цифр, используйте буквы!');
        }

        if ($validation->run()) {

            $user->update([
                'password' => password_hash($newpass, PASSWORD_BCRYPT),
            ]);

            $subject = 'Изменение пароля на сайте '.setting('title');
            $message = 'Здравствуйте, '.getUser('login').'<br>Вами была произведена операция по изменению пароля<br><br><b>Ваш новый пароль: '.$newpass.'</b><br>Сохраните его в надежном месте<br><br>Данные инициализации:<br>IP: '.getClientIp().'<br>Браузер: '.getUserAgent().'<br>Время: '.date('j.m.y / H:i', SITETIME);

            $body = view('mailer.default', compact('subject', 'message'), true);
            sendMail($user->email, $subject, $body);

            unset($_SESSION['id'], $_SESSION['password']);

            setFlash('success', 'Пароль успешно изменен!');
            redirect("/login");
        } else {
            setFlash('danger', $validation->getErrors());
            redirect("/account");
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

            $key = str_random();

            $user->update([
                'apikey' => md5(getUser('login').str_random()),
            ]);

            setFlash('success', 'Новый ключ успешно сгенерирован!');
        } else {
            setFlash('danger', 'Ошибка! Неверный идентификатор сессии, повторите действие!');
        }

        redirect("/account");
    }
}
