<?php

declare(strict_types=1);

namespace App\Http\Controllers\User;

use App\Classes\Validator;
use App\Http\Controllers\Controller;
use App\Models\BlackList;
use App\Models\ChangeMail;
use App\Models\Flood;
use App\Models\Invite;
use App\Models\User;
use App\Models\UserField;
use Exception;
use GuzzleHttp\Exception\GuzzleException;
use Illuminate\Database\Query\JoinClause;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\DB;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\View\View;

class UserController extends Controller
{
    /**
     * User profile
     *
     * @param string $login
     *
     * @return View
     */
    public function index(string $login): View
    {
        if (! $user = getUserByLogin($login)) {
            abort(404, __('validator.user'));
        }

        $user->load('lastBan');
        $adminGroups = User::ADMIN_GROUPS;
        $invite = Invite::query()->where('invite_user_id', $user->id)->first();

        $fields = UserField::query()
            ->select('uf.*', 'ud.value')
            ->from('user_fields as uf')
            ->leftJoin('user_data as ud', static function (JoinClause $join) use ($user) {
                $join->on('uf.id', 'ud.field_id')
                    ->where('ud.user_id', $user->id);
            })
            ->whereNotNull('ud.value')
            ->orderBy('uf.sort')
            ->get();

        return view('users/user', compact('user', 'invite', 'adminGroups', 'fields'));
    }

    /**
     * Note
     *
     * @param string    $login
     * @param Request   $request
     * @param Validator $validator
     *
     * @return View|RedirectResponse
     */
    public function note(string $login, Request $request, Validator $validator)
    {
        if (! isAdmin()) {
            abort(403, __('main.page_only_admins'));
        }

        if (! $user = getUserByLogin($login)) {
            abort(404, __('validator.user'));
        }

        if ($request->isMethod('post')) {
            $notice = $request->input('notice');

            $validator->equal($request->input('_token'), csrf_token(), ['notice' => __('validator.token')])
                ->length($notice, 0, 1000, ['notice' => __('users.note_to_big')]);

            if ($validator->isValid()) {
                $user->note()->updateOrCreate([], [
                    'text'         => $notice,
                    'edit_user_id' => getUser('id'),
                    'updated_at'   => SITETIME,
                ]);

                setFlash('success', __('users.note_saved_success'));

                return redirect('users/' . $user->login);
            }

            setInput($request->all());
            setFlash('danger', $validator->getErrors());
        }

        return view('users/note', compact('user'));
    }

    /**
     * Registration
     *
     * @param Request   $request
     * @param Validator $validator
     *
     * @return View|RedirectResponse
     * @throws GuzzleException
     */
    public function register(Request $request, Validator $validator)
    {
        if (getUser()) {
            abort(403, __('users.already_registered'));
        }

        if (! setting('openreg')) {
            abort(200, __('users.registration_suspended'));
        }

        if ($request->isMethod('post')) {
            if ($request->has(['login', 'password'])) {
                $login        = $request->input('login');
                $password     = $request->input('password');
                $password2    = $request->input('password2');
                $invite       = setting('invite') ? $request->input('invite') : '';
                $email        = strtolower($request->input('email'));
                $domain       = utfSubstr(strrchr($email, '@'), 1);
                $gender       = $request->input('gender') === User::MALE ? User::MALE : User::FEMALE;
                $level        = User::USER;
                $activateLink = null;
                $activateKey  = null;
                $invitation   = null;

                $validator->true(captchaVerify(), ['protect' => __('validator.captcha')])
                    ->regex($login, '|^[a-z0-9\-]+$|i', ['login' => __('validator.login')])
                    ->regex(utfSubstr($login, 0, 1), '|^[a-z0-9]+$|i', ['login' => __('users.login_begin_requirements')])
                    ->email($email, ['email' => __('validator.email')])
                    ->length($invite, 12, 16, ['invite' => __('users.invite_length_requirements')], (bool) setting('invite'))
                    ->length($login, 3, 20, ['login' => __('users.login_length_requirements')])
                    ->length($password, 6, 20, ['password' => __('users.password_length_requirements')])
                    ->equal($password, $password2, ['password2' => __('users.passwords_different')])
                    ->false(ctype_digit($login), ['login' => __('users.field_characters_requirements')])
                    ->false(ctype_digit($password), ['password' => __('users.field_characters_requirements')])
                    ->false(substr_count($login, '-') > 2, ['login' => __('users.login_hyphens_requirements')]);

                if (! empty($login)) {
                    // Проверка логина на существование
                    $checkLogin = User::query()->where('login', $login)->exists();
                    $validator->false($checkLogin, ['login' => __('users.login_already_exists')]);

                    // Проверка логина в черном списке
                    $blackLogin = Blacklist::query()
                        ->where('type', 'login')
                        ->where('value', strtolower($login))
                        ->exists();
                    $validator->false($blackLogin, ['login' => __('users.login_is_blacklisted')]);
                }

                // Проверка email на существование
                $checkMail = User::query()->where('email', $email)->exists();
                $validator->false($checkMail, ['email' => __('users.email_already_exists')]);

                // Проверка домена от email в черном списке
                $blackDomain = Blacklist::query()
                    ->where('type', 'domain')
                    ->where('value', $domain)
                    ->exists();
                $validator->false($blackDomain, ['email' => __('users.domain_is_blacklisted')]);

                // Проверка email в черном списке
                $blackMail = Blacklist::query()
                    ->where('type', 'email')
                    ->where('value', $email)
                    ->exists();
                $validator->false($blackMail, ['email' => __('users.email_is_blacklisted')]);

                // Проверка пригласительного ключа
                if (setting('invite')) {
                    $invitation = Invite::query()
                        ->select('id')
                        ->where('hash', $invite)
                        ->where('used', 0)
                        ->first();

                    $validator->notEmpty($invitation, ['invite' => __('users.invitation_invalid')]);
                }

                // Регистрация аккаунта
                if ($validator->isValid()) {
                    if (setting('regkeys')) {
                        $activateKey  = Str::random();
                        $activateLink = config('app.url') . '/key?code=' . $activateKey;
                        $level        = User::PENDED;
                    }

                    /* @var User $user */
                    $user = User::query()->create([
                        'login'         => $login,
                        'password'      => password_hash($password, PASSWORD_BCRYPT),
                        'email'         => $email,
                        'level'         => $level,
                        'gender'        => $gender,
                        'themes'        => setting('themes'),
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
                    $textNotice = textNotice('register', ['username' => $login]);
                    $user->sendMessage(null, $textNotice);

                    // --- Уведомление о регистрации на email ---//
                    $subject = 'Регистрация на ' . setting('title');
                    $message = 'Добро пожаловать, ' . $login . '<br>Теперь вы зарегистрированный пользователь сайта <a href="' . config('app.url') . '">' . setting('title') . '</a> , сохраните ваш логин и пароль в надежном месте, они вам еще пригодятся. <br>Ваши данные для входа на сайт <br><b>Логин: ' . $login . '</b><br><b>Пароль: ' . $password . '</b>';

                    $data = [
                        'to'           => $email,
                        'subject'      => $subject,
                        'text'         => $message,
                        'activateKey'  => $activateKey,
                        'activateLink' => $activateLink,
                    ];

                    sendMail('mailer.register', $data);

                    User::auth($login, $password);

                    setFlash('success', __('users.welcome', ['login' => $login]));

                    return redirect('/');
                }

                setInput($request->all());
                setFlash('danger', $validator->getErrors());
            }

            if ($request->has('token')) {
                if ($user = User::socialAuth($request->input('token'))) {
                    setFlash('success', __('users.welcome', ['login' => $user->getName()]));

                    return redirect($request->input('return', '/'));
                }
            }
        }

        return view('users/register');
    }

    /**
     * Login
     *
     * @param Request   $request
     * @param Validator $validator
     * @param Flood     $flood
     *
     * @return View|RedirectResponse
     * @throws GuzzleException
     */
    public function login(Request $request, Validator $validator, Flood $flood)
    {
        if (getUser()) {
            setFlash('danger', __('main.already_authorized'));

            return redirect('/');
        }

        $cookieLogin = $request->cookie('login');
        $isFlood     = $flood->isFlood();

        if ($request->isMethod('post')) {
            if ($request->has(['login', 'pass'])) {
                if ($isFlood) {
                    $validator->true(captchaVerify(), ['protect' => __('validator.captcha')]);
                }

                if ($validator->isValid()) {
                    $login    = utfLower($request->input('login'));
                    $pass     = $request->input('pass');
                    $remember = $request->boolean('remember');

                    /** @var User $user */
                    if ($user = User::auth($login, $pass, $remember)) {
                        setFlash('success', __('users.welcome', ['login' => $user->getName()]));

                        return redirect($request->input('return', '/'));
                    }

                    $flood->saveState(300);
                    setInput($request->all());
                    setFlash('danger', __('users.incorrect_login_or_password'));
                } else {
                    setInput($request->all());
                    setFlash('danger', $validator->getErrors());
                }

                return redirect('login');
            }

            if ($request->has('token')) {
                if ($user = User::socialAuth($request->input('token'))) {
                    setFlash('success', __('users.welcome', ['login' => $user->getName()]));

                    return redirect($request->input('return', '/'));
                }
            }
        }

        return view('users/login', compact('cookieLogin', 'isFlood'));
    }

    /**
     * Exit
     *
     * @param Request $request
     *
     * @return RedirectResponse
     */
    public function logout(Request $request): RedirectResponse
    {
        if ($request->input('_token') === csrf_token()) {
            $request->session()->flush();
            cookie()->queue(cookie()->forget('password'));
        } else {
            setFlash('danger', __('validator.token'));
        }

        return redirect('/');
    }

    /**
     * Profile editing
     *
     * @param Request   $request
     * @param Validator $validator
     *
     * @return View|RedirectResponse
     */
    public function profile(Request $request, Validator $validator)
    {
        if (! $user = getUser()) {
            abort(403, __('main.not_authorized'));
        }

        $fields = UserField::query()
            ->select('uf.*', 'ud.value')
            ->from('user_fields as uf')
            ->leftJoin('user_data as ud', static function (JoinClause $join) use ($user) {
                $join->on('uf.id', 'ud.field_id')
                    ->where('ud.user_id', $user->id);
            })
            ->orderBy('uf.sort')
            ->get();

        if ($request->isMethod('post')) {
            $info     = $request->input('info');
            $name     = $request->input('name');
            $country  = $request->input('country');
            $city     = $request->input('city');
            $phone    = preg_replace('/\D/', '', $request->input('phone') ?? '');
            $site     = $request->input('site');
            $birthday = $request->input('birthday');
            $gender   = $request->input('gender') === User::MALE ? User::MALE : User::FEMALE;

            $validator->equal($request->input('_token'), csrf_token(), __('validator.token'))
                ->url($site, ['site' => __('validator.site')], false)
                ->regex($birthday, '#^[0-9]{2}+\.[0-9]{2}+\.[0-9]{4}$#', ['birthday' => __('validator.date')], false)
                ->phone($phone, ['phone' => __('validator.phone')], false)
                ->length($info, 0, 1000, ['info' => __('users.info_yourself_long')])
                ->length($name, 3, 20, ['name' => __('users.name_short_or_long')], false);

            foreach ($fields as $field) {
                $validator->length(
                    $request->input('field' . $field->id),
                    $field->min,
                    $field->max,
                    ['field' . $field->id => 'Ошибка'],
                    $field->required
                );
            }

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

                foreach ($fields as $field) {
                    $user->data()
                        ->updateOrCreate([
                            'field_id' => $field->id,
                        ], [
                            'value' => $request->input('field' . $field->id),
                        ]);
                }

                setFlash('success', __('users.profile_success_changed'));

                return redirect('profile');
            }

            setInput($request->all());
            setFlash('danger', $validator->getErrors());
        }

        return view('users/profile', compact('user', 'fields'));
    }

    /**
     * Confirmation of registration
     *
     * @param Request   $request
     * @param Validator $validator
     *
     * @return View|RedirectResponse
     */
    public function key(Request $request, Validator $validator)
    {
        /* @var User $user */
        if (! $user = getUser()) {
            abort(403, __('main.not_authorized'));
        }

        if (! setting('regkeys')) {
            abort(200, __('users.confirm_registration_disabled'));
        }

        if ($user->level !== User::PENDED) {
            abort(403, __('users.profile_not_confirmation'));
        }

        /* Повторная отправка */
        if ($request->has('email') && $request->isMethod('post')) {
            $email  = strtolower($request->input('email'));
            $domain = utfSubstr(strrchr($email, '@'), 1);

            $validator->equal($request->input('_token'), csrf_token(), __('validator.token'))
                ->true(captchaVerify(), ['protect' => __('validator.captcha')])
                ->email($email, ['email' => __('validator.email')]);

            $regMail = User::query()->where('login', '<>', $user->login)->where('email', $email)->count();
            $validator->empty($regMail, ['email' => __('users.email_already_exists')]);

            $blackMail = BlackList::query()->where('type', 'email')->where('value', $email)->count();
            $validator->empty($blackMail, ['email' => __('users.email_is_blacklisted')]);

            $blackDomain = Blacklist::query()->where('type', 'domain')->where('value', $domain)->count();
            $validator->empty($blackDomain, ['email' => __('users.domain_is_blacklisted')]);

            if ($validator->isValid()) {
                $activateKey  = Str::random();
                $activateLink = config('app.url') . '/key?code=' . $activateKey;

                $user->update([
                    'email'         => $email,
                    'confirmregkey' => $activateKey,
                ]);

                /* Уведомление о регистрации на email */
                $subject = 'Регистрация на ' . setting('title');
                $message = 'Добро пожаловать, ' . e($user->getName()) . '<br>Теперь вы зарегистрированный пользователь сайта <a href="' . config('app.url') . '">' . setting('title') . '</a> , сохраните ваш логин и пароль в надежном месте, они вам еще пригодятся.';

                $data = [
                    'to'           => $email,
                    'subject'      => $subject,
                    'text'         => $message,
                    'activateKey'  => $activateKey,
                    'activateLink' => $activateLink,
                ];

                sendMail('mailer.register', $data);
                setFlash('success', __('users.confirm_code_success_sent'));

                return redirect('/');
            }

            setInput($request->all());
            setFlash('danger', $validator->getErrors());
        }

        /* Подтверждение кода */
        if ($request->has('code')) {
            $code = $request->input('code');

            if ($code === $user->confirmregkey) {
                $user->update([
                    'confirmregkey' => null,
                    'level'         => User::USER,
                ]);

                setFlash('success', __('users.account_success_activated'));

                return redirect('/');
            }

            setFlash('danger', __('users.confirm_code_invalid'));
        }

        return view('users/key', compact('user'));
    }

    /**
     * Settings
     *
     * @param Request   $request
     * @param Validator $validator
     *
     * @return View|RedirectResponse
     */
    public function setting(Request $request, Validator $validator)
    {
        if (! $user = getUser()) {
            abort(403, __('main.not_authorized'));
        }

        $setting['themes']    = array_map('basename', glob(public_path('/themes/*'), GLOB_ONLYDIR));
        $setting['languages'] = array_map('basename', glob(resource_path('lang/*'), GLOB_ONLYDIR));
        $setting['timezones'] = range(-12, 12);

        if ($request->isMethod('post')) {
            $themes    = $request->input('themes');
            $timezone  = $request->input('timezone', 0);
            $language  = $request->input('language');
            $notify    = $request->input('notify') ? 1 : 0;
            $subscribe = $request->input('subscribe') ? Str::random(32) : null;

            $validator->equal($request->input('_token'), csrf_token(), __('validator.token'))
                ->regex($themes, '|^[a-z0-9_\-]+$|i', ['themes' => __('users.theme_invalid')])
                ->true(in_array($themes, $setting['themes'], true) || empty($themes), ['themes' => __('users.theme_not_installed')])
                ->regex($language, '|^[a-z]+$|', ['language' => __('users.language_invalid')])
                ->in($language, $setting['languages'], ['language' => __('users.language_not_installed')])
                ->regex($timezone, '|^[\-\+]{0,1}[0-9]{1,2}$|', ['timezone' => __('users.timezone_invalid')]);

            if ($validator->isValid()) {
                $user->update([
                    'themes'    => $themes,
                    'timezone'  => $timezone,
                    'notify'    => $notify,
                    'subscribe' => $subscribe,
                    'language'  => $language,
                ]);

                setFlash('success', __('users.settings_success_changed'));

                return redirect('settings');
            }

            setInput($request->all());
            setFlash('danger', $validator->getErrors());
        }

        return view('users/settings', compact('user', 'setting'));
    }

    /**
     * User data
     *
     * @return View
     */
    public function account(): View
    {
        if (! $user = getUser()) {
            abort(403, __('main.not_authorized'));
        }

        return view('users/account', compact('user'));
    }

    /**
     * Initialize email change
     *
     * @param Request   $request
     * @param Validator $validator
     *
     * @return RedirectResponse
     */
    public function changeMail(Request $request, Validator $validator): RedirectResponse
    {
        if (! $user = getUser()) {
            abort(403, __('main.not_authorized'));
        }

        $email    = strtolower($request->input('email'));
        $password = $request->input('password');

        $validator->equal($request->input('_token'), csrf_token(), __('validator.token'))
            ->notEqual($email, $user->email, ['email' => __('users.email_different')])
            ->email($email, ['email' => __('validator.email')])
            ->true(password_verify($password, $user->password), ['password' => __('users.password_different')]);

        $regMail = User::query()->where('email', $email)->first();
        $validator->empty($regMail, ['email' => __('users.email_already_exists')]);

        // Проверка email в черном списке
        $blackMail = BlackList::query()->where('type', 'email')->where('value', $email)->first();
        $validator->empty($blackMail, ['email' => __('users.email_is_blacklisted')]);

        ChangeMail::query()->where('created_at', '<', SITETIME)->delete();

        $changeMail = ChangeMail::query()->where('user_id', $user->id)->first();
        $validator->empty($changeMail, __('users.confirm_already_sent'));

        if ($validator->isValid()) {
            $genkey = Str::random();

            $subject = 'Изменение email на ' . setting('title');
            $message = 'Здравствуйте, ' . e($user->getName()) . '<br>Вами была произведена операция по изменению адреса электронной почты<br><br>Для того, чтобы изменить email, необходимо подтвердить новый адрес почты<br>Перейдите по данной ссылке:<br><br><a href="' . config('app.url') . '/accounts/editmail?key=' . $genkey . '">' . config('app.url') . '/accounts/editmail?key=' . $genkey . '</a><br><br>Ссылка будет дейстительной в течение суток до ' . date('j.m.y / H:i', strtotime('+1 day', SITETIME)) . '<br>Для изменения адреса необходимо быть авторизованным на сайте<br>Если это сообщение попало к вам по ошибке или вы не собираетесь менять email, то просто проигнорируйте данное письмо';

            $data = [
                'to'      => $email,
                'subject' => $subject,
                'text'    => $message,
            ];

            sendMail('mailer.default', $data);

            changeMail::query()->create([
                'user_id'    => $user->id,
                'mail'       => $email,
                'hash'       => $genkey,
                'created_at' => strtotime('+1 hour', SITETIME),
            ]);

            setFlash('success', __('users.confirm_success_sent'));
        } else {
            setInput($request->all());
            setFlash('danger', $validator->getErrors());
        }

        return redirect('accounts');
    }

    /**
     * Email change
     *
     * @param Request   $request
     * @param Validator $validator
     *
     * @return RedirectResponse
     * @throws Exception
     */
    public function editMail(Request $request, Validator $validator): RedirectResponse
    {
        if (! $user = getUser()) {
            abort(403, __('main.not_authorized'));
        }

        $key = $request->input('key');

        ChangeMail::query()->where('created_at', '<', SITETIME)->delete();

        /** @var ChangeMail $changeMail */
        $changeMail = ChangeMail::query()->where('hash', $key)->where('user_id', $user->id)->first();

        $validator->notEmpty($key, __('users.changed_code_empty'))
            ->notEmpty($changeMail, __('users.changed_code_not_found'));

        if ($changeMail) {
            $validator->notEqual($changeMail->mail, $user->mail, __('users.email_different'));

            $regMail = User::query()->where('email', $changeMail->mail)->count();
            $validator->empty($regMail, __('users.email_already_exists'));

            $blackMail = BlackList::query()->where('type', 'email')->where('value', $changeMail->mail)->count();
            $validator->empty($blackMail, __('users.email_is_blacklisted'));
        }

        if ($validator->isValid()) {
            $user->update([
                'email' => $changeMail->mail,
            ]);

            $changeMail->delete();

            setFlash('success', __('users.email_success_changed'));
        } else {
            setFlash('danger', $validator->getErrors());
        }

        return redirect('accounts');
    }

    /**
     * Status change
     *
     * @param Request   $request
     * @param Validator $validator
     *
     * @return RedirectResponse
     */
    public function editStatus(Request $request, Validator $validator): RedirectResponse
    {
        if (! $user = getUser()) {
            abort(403, __('main.not_authorized'));
        }

        $status = $request->input('status');
        $status = ! empty($status) ? $status : null;
        $cost   = $status ? setting('editstatusmoney') : 0;

        $validator->equal($request->input('_token'), csrf_token(), __('validator.token'))
            ->empty($user->ban, ['status' => __('users.status_changed_not_ban')])
            ->notEqual($status, $user->status, ['status' => __('users.status_different')])
            ->gte($user->point, setting('editstatuspoint'), ['status' => __('users.status_points')])
            ->gte($user->money, $cost, ['status' => __('users.status_moneys')])
            ->length($status, 3, 20, ['status' => __('users.status_short_or_long')], false);

        if ($validator->isValid()) {
            $user->update([
                'status' => $status,
                'money'  => DB::raw('money - ' . $cost),
            ]);

            clearCache('status');
            setFlash('success', __('users.status_success_changed'));
        } else {
            setInput($request->all());
            setFlash('danger', $validator->getErrors());
        }

        return redirect('accounts');
    }

    /**
     * Color change
     *
     * @param Request   $request
     * @param Validator $validator
     *
     * @return RedirectResponse
     */
    public function editColor(Request $request, Validator $validator): RedirectResponse
    {
        if (! $user = getUser()) {
            abort(403, __('main.not_authorized'));
        }

        $color = $request->input('color');
        $color = ! empty($color) ? $color : null;
        $cost  = $color ? setting('editcolormoney') : 0;

        $validator->equal($request->input('_token'), csrf_token(), __('validator.token'))
            ->notEqual($color, $user->color, ['color' => __('users.color_different')])
            ->gte($user->point, setting('editcolorpoint'), ['color' => __('users.color_points')])
            ->gte($user->money, $cost, ['color' => __('users.color_moneys')])
            ->regex($color, '|^#+[A-f0-9]{6}$|', ['color' => __('validator.color')], false);

        if ($validator->isValid()) {
            $user->update([
                'color' => $color,
                'money' => DB::raw('money - ' . $cost),
            ]);

            setFlash('success', __('users.color_success_changed'));
        } else {
            setInput($request->all());
            setFlash('danger', $validator->getErrors());
        }

        return redirect('accounts');
    }

    /**
     * Password change
     *
     * @param Request   $request
     * @param Validator $validator
     *
     * @return RedirectResponse
     */
    public function editPassword(Request $request, Validator $validator): RedirectResponse
    {
        if (! $user = getUser()) {
            abort(403, __('main.not_authorized'));
        }

        $newpass  = $request->input('newpass');
        $newpass2 = $request->input('newpass2');
        $oldpass  = $request->input('oldpass');

        $validator->equal($request->input('_token'), csrf_token(), __('validator.token'))
            ->true(password_verify($oldpass, $user->password), ['oldpass' => __('users.password_different')])
            ->length($newpass, 6, 20, ['newpass' => __('users.password_length_requirements')])
            ->notEqual($user->login, $newpass, ['newpass' => __('users.login_different')])
            ->equal($newpass, $newpass2, ['newpass2' => __('users.passwords_different')]);

        if (ctype_digit($newpass)) {
            $validator->addError(['newpass' => __('users.field_characters_requirements')]);
        }

        if ($validator->isValid()) {
            $user->update([
                'password' => password_hash($newpass, PASSWORD_BCRYPT),
            ]);

            $subject = 'Изменение пароля на ' . setting('title');
            $message = 'Здравствуйте, ' . e($user->getName()) . '<br>Вами была произведена операция по изменению пароля<br><br><b>Ваш новый пароль: ' . $newpass . '</b><br>Сохраните его в надежном месте<br><br>Данные инициализации:<br>IP: ' . getIp() . '<br>Браузер: ' . getBrowser() . '<br>Время: ' . date('j.m.y / H:i', SITETIME);

            $data = [
                'to'        => $user->email,
                'subject'   => $subject,
                'text'      => $message,
            ];

            sendMail('mailer.default', $data);

            $request->session()->forget(['id', 'password']);

            setFlash('success', __('users.password_success_changed'));

            return redirect('login');
        }

        setInput($request->all());
        setFlash('danger', $validator->getErrors());

        return redirect('accounts');
    }

    /**
     * Key generation
     *
     * @param Request $request
     *
     * @return RedirectResponse
     */
    public function apikey(Request $request): RedirectResponse
    {
        if (! $user = getUser()) {
            abort(403, __('main.not_authorized'));
        }

        if ($request->input('_token') === csrf_token()) {
            $apiKey  = md5($user->login . Str::random());
            $message = __('users.token_success_changed');

            if ($request->input('action') === 'create') {
                $message = __('users.token_success_created');
            }

            if ($request->input('action') === 'delete') {
                $apiKey = '';
                $message = __('users.token_success_deleted');
            }

            $user->update([
                'apikey' => $apiKey
            ]);

            setFlash('success', $message);
        } else {
            setFlash('danger', __('validator.token'));
        }

        return redirect('accounts');
    }

    /**
     * Проверка доступности логина
     *
     * @param Request   $request
     * @param Validator $validator
     *
     * @return JsonResponse
     * @throws Exception
     */
    public function checkLogin(Request $request, Validator $validator): JsonResponse
    {
        $login = (string) $request->input('login');

        $validator
            ->true($request->ajax(), __('validator.not_ajax'))
            ->regex($login, '|^[a-z0-9\-]+$|i', __('validator.login'))
            ->regex(utfSubstr($login, 0, 1), '|^[a-z0-9]+$|i', __('users.login_begin_requirements'))
            ->length($login, 3, 20, __('users.login_length_requirements'))
            ->false(ctype_digit($login), __('users.field_characters_requirements'))
            ->false(substr_count($login, '-') > 2, __('users.login_hyphens_requirements'));

        if ($validator->isValid()) {
            $existLogin = User::query()
                ->where('login', $login)
                ->exists();

            $blackLogin = Blacklist::query()
                ->where('type', 'login')
                ->where('value', strtolower($login))
                ->exists();

            $validator
                ->false($existLogin, __('users.login_already_exists'))
                ->false($blackLogin, __('users.login_is_blacklisted'));
        }

        if ($validator->isValid()) {
            return response()->json(['success' => true]);
        }

        return response()->json([
            'success' => false,
            'message' => current($validator->getErrors()),
        ]);
    }
}
