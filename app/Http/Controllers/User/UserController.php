<?php

declare(strict_types=1);

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use App\Models\BlackList;
use App\Models\Flood;
use App\Models\User;
use App\Services\UserService;
use App\Support\Validator;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
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

        return view('users/user', compact('user', 'adminGroups'));
    }

    /**
     * Registration
     */
    public function register(Request $request, Validator $validator, UserService $userService): View|RedirectResponse
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
                $email = strtolower((string) $request->input('email'));
                $gender = $request->input('gender') === User::MALE ? User::MALE : User::FEMALE;

                $validator->true(captchaVerify(), ['protect' => __('validator.captcha')]);

                $userService->validateRegistration($validator, $login, $password, $password2, $email);

                // Регистрация аккаунта
                if ($validator->isValid()) {
                    $user = $userService->register($login, $password, $email, $gender);

                    Auth::login($user, true);

                    return redirect('/')
                        ->with('success', __('users.welcome', ['login' => $login]));
                }

                return redirect()->back()
                    ->withInput()
                    ->withErrors($validator->getErrors());
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

                    try {
                        $authorized = Auth::attempt($credentials, $remember);
                    } catch (\RuntimeException) {
                        return redirect('recovery')
                            ->withInput()
                            ->with('danger', __('users.password_reset_required'));
                    }

                    if ($authorized) {
                        $request->session()->regenerate();
                        $user = Auth::user();

                        return redirect()->intended()
                            ->with('success', __('users.welcome', ['login' => $user->getName()], $user->language));
                    }

                    $flood->saveState(300);

                    return redirect('login')
                        ->withInput()
                        ->with('danger', __('users.incorrect_login_or_password'));
                }

                return redirect('login')
                    ->withInput()
                    ->withErrors($validator->getErrors());
            }
        }

        // Запоминаем страницу, с которой пришёл гость, для возврата после входа
        $previous = url()->previous();
        if (! Str::contains($previous, ['/login', '/register', '/recovery'])) {
            $request->session()->put('url.intended', $previous);
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
    public function profile(Request $request, Validator $validator, UserService $userService): View|RedirectResponse
    {
        if (! $user = getUser()) {
            abort(403, __('main.not_authorized'));
        }

        if ($request->isMethod('post')) {
            $data = $userService->validateProfile($validator, $user, $request);

            if ($validator->isValid()) {
                $userService->saveProfile($user, $data, $request);

                return redirect('profile')
                    ->with('success', __('users.profile_success_changed'));
            }

            return redirect()->back()
                ->withInput()
                ->withErrors($validator->getErrors());
        }

        return view('users/profile', compact('user'));
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

            $validator
                ->false(User::query()->where('login', '<>', $user->login)->where('email', $email)->exists(), ['email' => __('users.email_already_exists')])
                ->false(BlackList::isBlacklisted('email', $email), ['email' => __('users.email_is_blacklisted')])
                ->false(BlackList::isBlacklisted('domain', $domain), ['email' => __('users.domain_is_blacklisted')]);

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

                return redirect()->route('verify')
                    ->with('success', __('users.confirm_code_success_sent'));
            }

            return redirect()->route('verify')
                ->withInput()
                ->withErrors($validator->getErrors());
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
    public function setting(Request $request, Validator $validator, UserService $userService): View|RedirectResponse
    {
        if (! $user = getUser()) {
            abort(403, __('main.not_authorized'));
        }

        $setting['themes'] = getAvailableThemes();
        $setting['languages'] = getAvailableLanguages();
        $setting['timezones'] = range(-12, 12);

        if ($request->isMethod('post')) {
            $data = $userService->validateSettings($validator, $request);

            if ($validator->isValid()) {
                $user->update($data);

                return redirect('settings')
                    ->with('success', __('users.settings_success_changed'));
            }

            return redirect()->back()
                ->withInput()
                ->withErrors($validator->getErrors());
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

            $validator
                ->false($existLogin, __('users.login_already_exists'))
                ->false(BlackList::isBlacklisted('login', strtolower($login)), __('users.login_is_blacklisted'));
        }

        if ($validator->isValid()) {
            return response()->json(['success' => true]);
        }

        return response()->json([
            'success' => false,
            'message' => current($validator->getErrors()),
        ]);
    }

    /**
     * Поиск пользователей для упоминаний
     */
    public function searchUsers(Request $request): JsonResponse
    {
        $query = (string) $request->input('query', '');

        if (mb_strlen($query) < 2) {
            return response()->json();
        }

        $users = User::query()
            ->where(function ($q) use ($query) {
                $q->where('login', 'like', $query . '%')
                    ->orWhere('name', 'like', $query . '%');
            })
            ->orderByDesc('point')
            ->limit(10)
            ->get(['login', 'name']);

        return response()->json($users);
    }
}
