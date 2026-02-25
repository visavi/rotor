<?php

declare(strict_types=1);

namespace App\Http\Controllers\User;

use App\Classes\Validator;
use App\Http\Controllers\Controller;
use App\Models\BlackList;
use App\Models\Flood;
use App\Models\Invite;
use App\Models\Login;
use App\Models\User;
use App\Models\UserField;
use Illuminate\Database\Query\JoinClause;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Illuminate\View\View;

class UserController extends Controller
{
    /**
     * User profile
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
     */
    public function note(string $login, Request $request, Validator $validator): View|RedirectResponse
    {
        if (! isAdmin()) {
            abort(403, __('main.page_only_admins'));
        }

        if (! $user = getUserByLogin($login)) {
            abort(404, __('validator.user'));
        }

        if ($request->isMethod('post')) {
            $notice = $request->input('notice');

            $validator->length($notice, 0, 1000, ['notice' => __('users.note_to_big')]);

            if ($validator->isValid()) {
                $user->note()->updateOrCreate([], [
                    'text'         => $notice,
                    'edit_user_id' => getUser('id'),
                    'updated_at'   => SITETIME,
                ]);

                return redirect()
                    ->route('users.user', ['login' => $user->login])
                    ->with('success', __('users.note_saved_success'));
            }

            $request->flash();
            $request->session()->flash('flash.danger', $validator->getErrors());
        }

        return view('users/note', compact('user'));
    }

    /**
     * Registration
     */
    public function register(Request $request, Validator $validator): View|RedirectResponse
    {
        if (getUser()) {
            abort(403, __('users.already_registered'));
        }

        if (! setting('openreg')) {
            abort(200, __('users.registration_suspended'));
        }

        if ($request->isMethod('post')) {
            if ($request->has(['login', 'password'])) {
                $login = (string) $request->input('login');
                $password = $request->input('password');
                $password2 = $request->input('password2');
                $invite = setting('invite') ? $request->input('invite') : '';
                $email = strtolower((string) $request->input('email'));
                $domain = Str::substr(strrchr($email, '@'), 1);
                $gender = $request->input('gender') === User::MALE ? User::MALE : User::FEMALE;
                $invitation = null;

                $validator->true(captchaVerify(), ['protect' => __('validator.captcha')])
                    ->regex($login, '|^[a-z0-9\-]+$|i', ['login' => __('validator.login')])
                    ->regex(Str::substr($login, 0, 1), '|^[a-z0-9]+$|i', ['login' => __('users.login_begin_requirements')])
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

                // Подтверждение регистрации
                $confirmToken = null;
                $confirmUrl = null;
                if (setting('regkeys')) {
                    $confirmToken = Str::random(32);
                    $confirmUrl = route('confirm', ['token' => $confirmToken]);
                }

                // Регистрация аккаунта
                if ($validator->isValid()) {
                    $user = User::query()->create([
                        'login'         => $login,
                        'password'      => Hash::make($password),
                        'email'         => $email,
                        'level'         => setting('regkeys') ? User::PENDED : User::USER,
                        'gender'        => $gender,
                        'themes'        => setting('themes'),
                        'point'         => 0,
                        'language'      => setting('language'),
                        'money'         => setting('registermoney'),
                        'subscribe'     => Str::random(32),
                        'confirm_token' => $confirmToken,
                        'updated_at'    => SITETIME,
                        'created_at'    => SITETIME,
                    ]);

                    // Активация пригласительного ключа
                    if ($invitation && setting('invite')) {
                        $invitation->update([
                            'used'           => true,
                            'used_at'        => SITETIME,
                            'invite_user_id' => $user->id,
                        ]);
                    }

                    // ----- Уведомление в приват ----//
                    $textNotice = textNotice('register', ['username' => $login]);
                    $user->sendMessage(null, $textNotice);

                    // --- Уведомление о регистрации на email ---//
                    $subject = 'Регистрация на ' . setting('title');
                    $data = [
                        'to'         => $email,
                        'subject'    => $subject,
                        'login'      => $login,
                        'password'   => $password,
                        'confirmUrl' => $confirmUrl,
                    ];

                    sendMail('mailer.register', $data);

                    Auth::login($user, true);

                    setFlash('success', __('users.welcome', ['login' => $login]));

                    return redirect('/');
                }

                setInput($request->all());
                setFlash('danger', $validator->getErrors());
            }
        }

        return view('users/register');
    }

    /**
     * Login
     */
    public function login(Request $request, Validator $validator, Flood $flood): View|RedirectResponse
    {
        if (Auth::check()) {
            return redirect('/')->with('danger', __('main.already_authorized'));
        }

        $isFlood = $flood->isFlood();

        if ($request->isMethod('post')) {
            if ($request->has(['login', 'password'])) {
                if ($isFlood) {
                    $validator->true(captchaVerify(), ['protect' => __('validator.captcha')]);
                }

                if ($validator->isValid()) {
                    $login = Str::lower((string) $request->input('login'));
                    $password = $request->input('password');
                    $remember = $request->boolean('remember');

                    $field = strpos($request->input('login'), '@') ? 'email' : 'login';

                    $credentials = [
                        $field     => $login,
                        'password' => $password,
                    ];

                    if (Auth::attempt($credentials, $remember)) {
                        $request->session()->regenerate();
                        $user = Auth::user();

                        $user->saveVisit(Login::AUTH);

                        return redirect($request->input('return', '/'))
                            ->with('success', __('users.welcome', ['login' => $user->getName()], $user->language));
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
        }

        return view('users/login', compact('isFlood'));
    }

    /**
     * Exit
     */
    public function logout(Request $request): RedirectResponse
    {
        Auth::logout();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect('/');
    }

    /**
     * Profile editing
     */
    public function profile(Request $request, Validator $validator): View|RedirectResponse
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
            $info = $request->input('info');
            $name = $request->input('name');
            $country = $request->input('country');
            $city = $request->input('city');
            $phone = preg_replace('/[^\d+]/', '', $request->input('phone') ?? '');
            $site = $request->input('site');
            $birthday = $request->input('birthday');
            $gender = $request->input('gender') === User::MALE ? User::MALE : User::FEMALE;

            $validator
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
                    ['field' . $field->id => __('validator.text')],
                    $field->required
                );
            }

            if ($validator->isValid()) {
                $country = Str::substr($country, 0, 30);
                $city = Str::substr($city, 0, 50);

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
     * Verify registration
     */
    public function verify(Request $request, Validator $validator): View|RedirectResponse
    {
        if (! $user = $request->user()) {
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
            $email = strtolower((string) $request->input('email'));
            $domain = Str::substr(strrchr($email, '@'), 1);

            $validator
                ->true(captchaVerify(), ['protect' => __('validator.captcha')])
                ->email($email, ['email' => __('validator.email')]);

            $regMail = User::query()->where('login', '<>', $user->login)->where('email', $email)->count();
            $validator->empty($regMail, ['email' => __('users.email_already_exists')]);

            $blackMail = BlackList::query()->where('type', 'email')->where('value', $email)->count();
            $validator->empty($blackMail, ['email' => __('users.email_is_blacklisted')]);

            $blackDomain = Blacklist::query()->where('type', 'domain')->where('value', $domain)->count();
            $validator->empty($blackDomain, ['email' => __('users.domain_is_blacklisted')]);

            if ($validator->isValid()) {
                $token = Str::random(32);
                $confirmUrl = route('confirm', ['token' => $token]);

                $user->update([
                    'email'         => $email,
                    'confirm_token' => $token,
                ]);

                /* Уведомление о регистрации на email */
                $subject = 'Регистрация на ' . setting('title');
                $data = [
                    'to'         => $email,
                    'subject'    => $subject,
                    'login'      => $user->login,
                    'password'   => '*****',
                    'confirmUrl' => $confirmUrl,
                ];

                sendMail('mailer.register', $data);
                setFlash('success', __('users.confirm_code_success_sent'));

                return redirect()->route('verify');
            }

            setInput($request->all());
            setFlash('danger', $validator->getErrors());
        }

        return view('users/verify', compact('user'));
    }

    /**
     * Confirm registration
     */
    public function confirm(string $token): RedirectResponse
    {
        $user = User::query()->where('confirm_token', $token)->first();
        if (! $user) {
            abort(200, __('users.confirm_code_invalid'));
        }

        $user->update([
            'level'         => User::USER,
            'confirm_token' => null,
        ]);

        return redirect('/')->with('success', __('users.account_success_activated'));
    }

    /**
     * Settings
     */
    public function setting(Request $request, Validator $validator): View|RedirectResponse
    {
        if (! $user = getUser()) {
            abort(403, __('main.not_authorized'));
        }

        $setting['themes'] = array_map('basename', glob(resource_path('views/themes/*'), GLOB_ONLYDIR));
        $setting['languages'] = array_map('basename', glob(resource_path('lang/*'), GLOB_ONLYDIR));
        $setting['timezones'] = range(-12, 12);

        if ($request->isMethod('post')) {
            $themes = $request->input('themes');
            $timezone = $request->input('timezone', 0);
            $language = $request->input('language');
            $notify = $request->input('notify') ? 1 : 0;
            $subscribe = $request->input('subscribe') ? Str::random(32) : null;

            $validator
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
     * Проверка доступности логина
     */
    public function checkLogin(Request $request, Validator $validator): JsonResponse
    {
        $login = (string) $request->input('login');

        $validator
            ->true($request->ajax(), __('validator.not_ajax'))
            ->regex($login, '|^[a-z0-9\-]+$|i', __('validator.login'))
            ->regex(Str::substr($login, 0, 1), '|^[a-z0-9]+$|i', __('users.login_begin_requirements'))
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
