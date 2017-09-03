<?php

namespace App\Controllers;

use App\Classes\Request;
use App\Classes\Validation;
use App\Models\BlackList;
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

        $note = Note::where('user_id', $user->id)->first();
        $invite = Invite::where('invite_user_id', $user->id)->first();

        return view('pages/user', compact('user', 'invite', 'note'));
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
                    'edit_user_id' => getUserId(),
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
        if (! isUser()) {
            abort(403, 'Для изменения рейтинга небходимо авторизоваться!');
        }

        $user = User::where('login', $login)->first();

        if (! $user) {
            abort('default', 'Данного пользователя не существует!');
        }

        if (getUserId() == $user->id) {
            abort('default', 'Запрещено изменять репутацию самому себе!');
        }

        if (user('point') < setting('editratingpoint')) {
            abort('default', 'Для изменения репутации необходимо набрать '.points(setting('editratingpoint')).'!');
        }

        // Голосовать за того же пользователя можно через 90 дней
        $getRating = Rating::where('user_id', getUserId())
            ->where('recipient_id', $user->id)
            ->where('created_at', '>', SITETIME - 3600 * 24 * 90)
            ->first();

        if ($getRating) {
            abort('default', 'Вы уже изменяли репутацию этому пользователю!');
        }

        $vote = Request::input('vote') ? 1 : 0;

        if (Request::isMethod('post')) {

            $token = check(Request::input('token'));
            $text = check(Request::input('text'));

            $validation = new Validation();

            $validation->addRule('equal', [$token, $_SESSION['token']], 'Неверный идентификатор сессии, повторите действие!')
                ->addRule('string', $text, ['text' => 'Слишком длинный или короткий комментарий!'], true, 5, 250);

            if (user('rating') < 10 && empty($vote)) {
                $validation->addError('Уменьшать репутацию могут только пользователи с рейтингом 10 или выше!');
            }

            if ($validation->run()) {

                $text = antimat($text);

                Rating::create([
                    'user_id'      => getUserId(),
                    'recipient_id' => $user->id,
                    'text'         => $text,
                    'vote'         => $vote,
                    'created_at'   => SITETIME,
                ]);

                if ($vote == 1) {
                    $text = 'Пользователь [b]' . getUsername() . '[/b] поставил вам плюс! (Ваш рейтинг: ' . ($user['rating'] + 1) . ')' . PHP_EOL . 'Комментарий: ' . $text;

                    $user->update([
                        'rating' => DB::raw('posrating - negrating + 1'),
                        'posrating' => DB::raw('posrating + 1'),
                    ]);

                } else {

                    $text = 'Пользователь [b]' . getUsername() . '[/b] поставил вам минус! (Ваш рейтинг: ' . ($user['rating'] - 1) . ')' . PHP_EOL . 'Комментарий: ' . $text;

                    $user->update([
                        'rating' => DB::raw('posrating - negrating - 1'),
                        'negrating' => DB::raw('negrating + 1'),
                    ]);
                }

                sendPrivate($user->id, getUserId(), $text);

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
        if (isUser()) {
            abort('403', 'Вы уже регистрировались, запрещено создавать несколько аккаунтов!');
        }

        if (! setting('openreg')) {
            abort('default', 'Регистрация временно приостановлена, пожалуйста зайдите позже!');
        }

        if (Request::isMethod('post')) {
            if (Request::has('logs') && Request::has('pars')) {
                $logs = check(Request::input('logs'));
                $pars = trim(Request::input('pars'));
                $pars2 = trim(Request::input('pars2'));
                $protect = check(strtolower(Request::input('protect')));
                $invite = setting('invite') ? check(Request::input('invite')) : '';
                $meil = strtolower(check(Request::input('meil')));
                $domain = utfSubstr(strrchr($meil, '@'), 1);
                $gender = Request::input('gender') == 1 ? 1 : 2;
                $activateKey = '';

                $validation = new Validation();
                $validation->addRule('equal', [$protect, $_SESSION['protect']], ['protect' => 'Проверочное число не совпало с данными на картинке!'])
                    ->addRule('regex', [$logs, '|^[a-z0-9\-]+$|i'], ['logs' => 'Недопустимые символы в логине. Разрешены знаки латинского алфавита, цифры и дефис!'], true)
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
                    $checkLogin = User::whereRaw('LOWER(login) = ?', [strtolower($logs)])->count();
                    $validation->addRule('empty', $checkLogin, ['logs' => 'Пользователь с данным логином уже зарегистрирован!']);

                    // Проверка логина в черном списке
                    $blackLogin = Blacklist::where('type', 2)
                        ->where('value', strtolower($logs))
                        ->count();
                    $validation->addRule('empty', $blackLogin, ['logs' => 'Выбранный вами логин занесен в черный список!']);
                }

                // Проверка email на существование
                $checkMail = User::where('email', $meil)->count();
                $validation->addRule('empty', $checkMail, ['meil' => 'Указанный вами адрес email уже используется в системе!']);

                // Проверка домена от email в черном списке
                $blackDomain = Blacklist::where('type', 3)
                    ->where('value', $domain)
                    ->count();
                $validation->addRule('empty', $blackDomain, ['meil' => 'Домен от вашего адреса email занесен в черный список!']);

                // Проверка email в черном списке
                $blackMail = Blacklist::where('type', 1)
                    ->where('value', $meil)
                    ->count();
                $validation->addRule('empty', $blackMail, ['meil' => 'Указанный вами адрес email занесен в черный список!']);

                // Проверка пригласительного ключа
                if (setting('invite')) {
                    $invitation = Invite::select('id')
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

                    $user = User::create([
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

                    User::login($logs, $pars);

                    setFlash('success', 'Добро пожаловать, ' . $logs . '!');
                    redirect('/');

                } else {
                    setInput(Request::all());
                    setFlash('danger', $validation->getErrors());
                }
            }

            if (Request::has('token')) {
                User::socialLogin(Request::input('token'));
            }
        }

        return view('pages/registration');
    }

    /**
     * Авторизация
     */
    public function login()
    {
        if (isUser()) {
            abort('403', 'Вы уже авторизованы!');
        }

        $cooklog = (isset($_COOKIE['login'])) ? check($_COOKIE['login']): '';
        if (Request::isMethod('post')) {
            if (Request::has('login') && Request::has('pass')) {
                $return = Request::input('return', '');
                $login = check(utfLower(Request::input('login')));
                $pass = trim(Request::input('pass'));
                $remember = Request::input('remember');

                if ($user = User::login($login, $pass, $remember)) {
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
                User::socialLogin(Request::input('token'));
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
        if (! $user = isUser()) {
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
        if (! $user = isUser()) {
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

            if ($code == user('confirmregkey')) {

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
}
