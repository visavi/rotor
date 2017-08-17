<?php

class UserController extends BaseController
{
    /**
     * Главная страница
     */
    public function index($login)
    {
        if (! $user = user($login)) {
            App::abort('default', 'Пользователя с данным логином не существует!');
        }

        App::view('pages/user', compact('user'));
    }

    /**
     * Заметка
     */
    public function note($login)
    {
        if (! is_admin()) {
            App::abort(403, 'Данная страница доступна только администрации!');
        }

        if (! $user = user($login)) {
            App::abort('default', 'Пользователя с данным логином не существует!');
        }

        $note = Note::where('user_id', $user->id)->first();

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
                    'edit_user_id' => App::getUserId(),
                    'updated_at'   => SITETIME,
                ];

                Note::saveNote($note, $record);

                App::setFlash('success', 'Заметка успешно сохранена!');
                App::redirect('/user/'.$user->login);

            } else {
                App::setInput(Request::all());
                App::setFlash('danger', $validation->getErrors());
            }
        }

        App::view('pages/user_note', compact('note', 'user'));
    }

    /**
     * Изменение рейтинга
     */
    public function rating($login)
    {
        if (! is_user()) {
            App::abort(403, 'Для изменения рейтинга небходимо авторизоваться!');
        }

        $user = User::where('login', $login)->first();

        if (! $user) {
            App::abort('default', 'Данного пользователя не существует!');
        }

        if (App::getUserId() == $user->id) {
            App::abort('default', 'Запрещено изменять репутацию самому себе!');
        }

        if (App::user('point') < Setting::get('editratingpoint')) {
            App::abort('default', 'Для изменения репутации необходимо набрать '.points(Setting::get('editratingpoint')).'!');
        }

        // Голосовать за того же пользователя можно через 90 дней
        $getRating = Rating::where('user_id', App::getUserId())
            ->where('recipient_id', $user->id)
            ->where('created_at', '>', SITETIME - 3600 * 24 * 90)
            ->first();

        if ($getRating) {
            App::abort('default', 'Вы уже изменяли репутацию этому пользователю!');
        }

        $vote = Request::input('vote') ? 1 : 0;

        if (Request::isMethod('post')) {

            $token = check(Request::input('token'));
            $text = check(Request::input('text'));

            $validation = new Validation();

            $validation->addRule('equal', [$token, $_SESSION['token']], 'Неверный идентификатор сессии, повторите действие!')
                ->addRule('string', $text, ['text' => 'Слишком длинный или короткий комментарий!'], true, 5, 250);

            if (App::user('rating') < 10 && empty($vote)) {
                $validation->addError('Уменьшать репутацию могут только пользователи с рейтингом 10 или выше!');
            }

            if ($validation->run()) {

                $text = antimat($text);

                Rating::create([
                    'user_id'      => App::getUserId(),
                    'recipient_id' => $user->id,
                    'text'         => $text,
                    'vote'         => $vote,
                    'created_at'   => SITETIME,
                ]);

                if ($vote == 1) {
                    $text = 'Пользователь [b]' . App::getUsername() . '[/b] поставил вам плюс! (Ваш рейтинг: ' . ($user['rating'] + 1) . ')' . PHP_EOL . 'Комментарий: ' . $text;

                    $user->update([
                        'rating' => Capsule::raw('posrating - negrating + 1'),
                        'posrating' => Capsule::raw('posrating + 1'),
                    ]);

                } else {

                    $text = 'Пользователь [b]' . App::getUsername() . '[/b] поставил вам минус! (Ваш рейтинг: ' . ($user['rating'] - 1) . ')' . PHP_EOL . 'Комментарий: ' . $text;

                    $user->update([
                        'rating' => Capsule::raw('posrating - negrating - 1'),
                        'negrating' => Capsule::raw('negrating + 1'),
                    ]);
                }

                send_private($user->id, App::getUserId(), $text);

                App::setFlash('success', 'Репутация успешно изменена!');
                App::redirect('/user/'.$user->login);
            } else {
                App::setInput(Request::all());
                App::setFlash('danger', $validation->getErrors());
            }
        }

        App::view('pages/rating', compact('user', 'vote'));
    }

    /**
     * Регистрация
     */
    public function register()
    {
        if (is_user()) {
            App::abort('403', 'Вы уже регистрировались, запрещено создавать несколько аккаунтов!');
        }

        if (! Setting::get('openreg')) {
            App::abort('default', 'Регистрация временно приостановлена, пожалуйста зайдите позже!');
        }

        if (Request::isMethod('post')) {
            if (Request::has('logs') && Request::has('pars')) {
                $logs = check(Request::input('logs'));
                $pars = trim(Request::input('pars'));
                $pars2 = trim(Request::input('pars2'));
                $protect = check(strtolower(Request::input('protect')));
                $invite = (!empty(Setting::get('invite'))) ? check(Request::input('invite')) : '';
                $meil = strtolower(check(Request::input('meil')));
                $domain = utf_substr(strrchr($meil, '@'), 1);
                $gender = Request::input('gender') == 1 ? 1 : 2;
                $activateKey = '';

                $validation = new Validation();
                $validation->addRule('equal', [$protect, $_SESSION['protect']], ['protect' => 'Проверочное число не совпало с данными на картинке!'])
                    ->addRule('regex', [$logs, '|^[a-z0-9\-]+$|i'], ['logs' => 'Недопустимые символы в логине. Разрешены знаки латинского алфавита, цифры и дефис!'], true)
                    ->addRule('email', $meil, ['meil' => 'Вы ввели неверный адрес email, необходим формат name@site.domen!'], true)
                    ->addRule('string', $invite, ['invite' => 'Слишком длинный или короткий пригласительный ключ!'], Setting::get('invite'), 12, 15)
                    ->addRule('string', $logs, ['logs' => 'Слишком длинный или короткий логин!'], true, 3, 20)
                    ->addRule('string', $pars, ['pars' => 'Слишком длинный или короткий пароль!'], true, 6, 20)
                    ->addRule('equal', [$pars, $pars2], ['pars2' => 'Ошибка! Введенные пароли отличаются друг от друга!']);

                if (ctype_digit($pars)) {
                    $validation->addError(['pars' => 'Запрещен пароль состоящий только из цифр, используйте буквы!']);
                }

                if (substr_count($logs, '-') > 2) {
                    $validation->addError(['logs' => 'Запрещено использовать в логине слишком много дефисов!']);
                }

                if (!empty($logs)) {
                    // Проверка логина на существование
                    $reglogin = DB::run()->querySingle("SELECT `id` FROM `users` WHERE LOWER(`login`)=? LIMIT 1;", [strtolower($logs)]);
                    $validation->addRule('empty', $reglogin, ['logs' => 'Пользователь с данным логином уже зарегистрирован!']);

                    // Проверка логина в черном списке
                    $blacklogin = DB::run()->querySingle("SELECT `id` FROM `blacklist` WHERE `type`=? AND `value`=? LIMIT 1;", [2, strtolower($logs)]);
                    $validation->addRule('empty', $blacklogin, ['logs' => 'Выбранный вами логин занесен в черный список!']);
                }

                // Проверка email на существование
                $regmail = DB::run()->querySingle("SELECT `id` FROM `users` WHERE `email`=? LIMIT 1;", [$meil]);
                $validation->addRule('empty', $regmail, ['meil' => 'Указанный вами адрес email уже используется в системе!']);

                // Проверка домена от email в черном списке
                $blackdomain = DB::run()->querySingle("SELECT `id` FROM `blacklist` WHERE `type`=? AND `value`=? LIMIT 1;", [3, $domain]);
                $validation->addRule('empty', $blackdomain, ['meil' => 'Домен от вашего адреса email занесен в черный список!']);

                // Проверка email в черном списке
                $blackmail = DB::run()->querySingle("SELECT `id` FROM `blacklist` WHERE `type`=? AND `value`=? LIMIT 1;", [1, $meil]);
                $validation->addRule('empty', $blackmail, ['meil' => 'Указанный вами адрес email занесен в черный список!']);

                // Проверка пригласительного ключа
                if (!empty(Setting::get('invite'))) {
                    $invitation = DB::run()->querySingle("SELECT `id` FROM `invite` WHERE `hash`=? AND `used`=? LIMIT 1;", [$invite, 0]);
                    $validation->addRule('not_empty', $invitation, ['invite' => 'Ключ приглашения недействителен!']);
                }

                // Регистрация аккаунта
                if ($validation->run()) {

                    // --- Уведомление о регистрации на email ---//
                    $message = 'Добро пожаловать, ' . $logs . '<br>Теперь вы зарегистрированный пользователь сайта <a href="' . Setting::get('home') . '">' . Setting::get('title') . '</a> , сохраните ваш пароль и логин в надежном месте, они вам еще пригодятся. <br>Ваши данные для входа на сайт <br><b>Логин: ' . $logs . '</b><br><b>Пароль: ' . $pars . '</b><br><br>';

                    if (Setting::get('regkeys') == 1) {
                        $siteLink = starts_with(Setting::get('home'), '//') ? 'http:'. Setting::get('home') : Setting::get('home');
                        $activateKey = str_random();
                        $activateLink = $siteLink.'/key?code=' . $activateKey;

                        echo '<b><span style="color:#ff0000">Внимание! После входа на сайт, вам будет необходимо ввести мастер-ключ для подтверждения регистрации<br>';
                        echo 'Мастер-ключ был выслан вам на почтовый ящик: ' . $meil . '</span></b><br><br>';
                    }

                    if (Setting::get('regkeys') == 2) {
                        echo '<b><span style="color:#ff0000">Внимание! Ваш аккаунт будет активирован только после проверки администрацией!</span></b><br><br>';
                    }

                    // Активация пригласительного ключа
                    if (!empty(Setting::get('invite'))) {
                        DB::run()->query("UPDATE `invite` SET `used`=?, `invited`=? WHERE `key`=? LIMIT 1;", [1, $logs, $invite]);
                    }

                    $user = User::create([
                        'login' => $logs,
                        'password' => password_hash($pars, PASSWORD_BCRYPT),
                        'email' => $meil,
                        'joined' => SITETIME,
                        'level' => 107,
                        'gender' => $gender,
                        'themes' => 0,
                        'point' => 0,
                        'money' => Setting::get('registermoney'),
                        'timelastlogin' => SITETIME,
                        'confirmreg' => Setting::get('regkeys'),
                        'confirmregkey' => $activateKey,
                        'subscribe' => str_random(32),
                    ]);

                    // ----- Уведомление в приват ----//
                    $textpriv = text_private(1, ['%USERNAME%' => $logs, '%SITENAME%' => Setting::get('home')]);
                    send_private($user->id, 0, $textpriv);

                    $subject = 'Регистрация на сайте ' . Setting::get('title');
                    $body = App::view('mailer.register', compact('subject', 'message', 'activateKey', 'activateLink'), true);
                    App::sendMail($meil, $subject, $body);

                    App::login($logs, $pars);

                    App::setFlash('success', 'Добро пожаловать, ' . $logs . '!');
                    App::redirect('/');

                } else {
                    App::setInput(Request::all());
                    App::setFlash('danger', $validation->getErrors());
                }
            }

            if (Request::has('token')) {
                App::socialLogin(Request::input('token'));
            }
        }

        App::view('pages/registration');
    }

    /**
     * Авторизация
     */
    public function login()
    {
        if (is_user()) {
            App::abort('403', 'Вы уже авторизованы!');
        }

        $cooklog = (isset($_COOKIE['login'])) ? check($_COOKIE['login']): '';
        if (Request::isMethod('post')) {
            if (Request::has('login') && Request::has('pass')) {
                $return = Request::input('return', '');
                $login = check(utf_lower(Request::input('login')));
                $pass = trim(Request::input('pass'));
                $remember = Request::input('remember');

                if ($user = App::login($login, $pass, $remember)) {
                    App::setFlash('success', 'Добро пожаловать, '.$user->login.'!');

                    if ($return) {
                        App::redirect($return);
                    } else {
                        App::redirect('/');
                    }
                }

                App::setInput(Request::all());
                App::setFlash('danger', 'Ошибка авторизации. Неправильный логин или пароль!');
            }

            if (Request::has('token')) {
                App::socialLogin(Request::input('token'));
            }
        }

        App::view('pages/login', compact('cooklog'));
    }

    /**
     * Выход
     */
    public function logout()
    {
        $domain = check_string(Setting::get('home'));

        $_SESSION = [];
        setcookie('password', '', SITETIME - 3600, '/', $domain, null, true);
        setcookie(session_name(), '', SITETIME - 3600, '/', '');
        session_destroy();

        App::redirect('/');
    }

    /**
     * Редактирование профиля
     */
    public function profile()
    {
        if (! is_user()) {
            App::abort(403, 'Авторизуйтесь для изменения данных в профиле!');
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

                $name    = utf_substr($name, 0, 20);
                $country = utf_substr($country, 0, 30);
                $city    = utf_substr($city, 0, 50);

                DB::run()->query("UPDATE `users` SET `name`=?, `country`=?, `city`=?, `icq`=?, `skype`=?, `site`=?, `birthday`=?, `info`=? WHERE `login`=? LIMIT 1;", [$name, $country, $city, $icq, $skype, $site, $birthday, $info, App::getUsername()]);

                App::setFlash('success', 'Ваш профиль успешно изменен!');
                App::redirect("/profile");

            } else {
                App::setInput(Request::all());
                App::setFlash('danger', $validation->getErrors());
            }
        }

        App::view('pages/profile', compact('udata'));
    }
}
