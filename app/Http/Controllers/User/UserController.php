<?php

declare(strict_types=1);

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use App\Http\Requests\User\ProfileRequest;
use App\Http\Requests\User\RegisterRequest;
use App\Http\Requests\User\SettingsRequest;
use App\Http\Requests\User\UserRules;
use App\Http\Requests\User\VerifyRequest;
use App\Models\Flood;
use App\Models\User;
use App\Services\UserService;
use App\Support\Locale;
use Illuminate\Container\Attributes\CurrentUser;
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
        $user = User::findByLoginOrFail($login);

        $user->load('lastBan');
        $adminGroups = User::ADMIN_GROUPS;

        return view('users/user', compact('user', 'adminGroups'));
    }

    /**
     * Registration
     */
    public function register(): View
    {
        if (auth()->check()) {
            abort(403, __('users.already_registered'));
        }

        if (! setting('openreg')) {
            abort(200, __('users.registration_suspended'));
        }

        return view('users/register');
    }

    /**
     * Создание аккаунта
     */
    public function storeRegister(RegisterRequest $request, UserService $userService): RedirectResponse
    {
        $login = $request->validated('login');

        $user = $userService->register($login, $request->validated('password'), $request->email(), $request->gender(), $request);

        Auth::login($user, true);

        return redirect('/')
            ->with('success', __('users.welcome', ['login' => $login]));
    }

    /**
     * Login
     */
    public function login(Request $request, Flood $flood): View|RedirectResponse
    {
        if (Auth::check()) {
            return redirect('/')->with('danger', __('main.already_authorized'));
        }

        $isFlood = $flood->isFlood();

        // Запоминаем страницу, с которой пришёл гость, для возврата после входа.
        // previous() берёт адрес из Referer: ссылка на вход с чужого сайта увела бы туда после входа
        $previous = url()->previous();
        $isOwnPage = parse_url($previous, PHP_URL_HOST) === $request->getHost();

        if ($isOwnPage && ! Str::contains($previous, ['/login', '/register', '/recovery'])) {
            $request->session()->put('url.intended', $previous);
        }

        return view('users/login', compact('isFlood'));
    }

    /**
     * Вход по логину или почте
     */
    public function authenticate(Request $request, Flood $flood): RedirectResponse
    {
        if (Auth::check()) {
            return redirect('/')->with('danger', __('main.already_authorized'));
        }

        if (! $request->has(['login', 'password'])) {
            return redirect('login');
        }

        // Капча — после неудачных попыток
        if ($flood->isFlood() && ! captchaVerify()) {
            return redirect('login')
                ->withInput()
                ->withErrors(['protect' => __('validator.captcha')]);
        }

        $login = $request->input('login');
        $password = $request->input('password');
        $login = is_string($login) ? mb_strtolower($login) : '';
        $field = str_contains($login, '@') ? 'email' : 'login';

        $credentials = [
            $field     => $login,
            'password' => is_string($password) ? $password : '',
        ];

        try {
            $authorized = Auth::attempt($credentials, $request->boolean('remember'));
        } catch (\RuntimeException) {
            return redirect('recovery')
                ->withInput()
                ->with('danger', __('users.password_reset_required'));
        }

        if ($authorized) {
            $request->session()->regenerate();
            $user = Auth::user() ?? abort(403);

            return redirect()->intended()
                ->with('success', __('users.welcome', ['login' => $user->getName()], $user->language));
        }

        $flood->saveState(300);

        return redirect('login')
            ->withInput()
            ->with('danger', __('users.incorrect_login_or_password'));
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
    public function profile(#[CurrentUser] User $user): View
    {
        return view('users/profile', compact('user'));
    }

    /**
     * Сохранение анкеты
     */
    public function updateProfile(ProfileRequest $request, UserService $userService, #[CurrentUser] User $user): RedirectResponse
    {
        $userService->saveProfile($user, $request->profile(), $request);

        return redirect('profile')
            ->with('success', __('users.profile_success_changed'));
    }

    /**
     * Verify registration
     */
    public function verify(Request $request): View
    {
        $user = $this->pendingUser($request);

        return view('users/verify', compact('user'));
    }

    /**
     * Повторная отправка письма подтверждения
     */
    public function resendVerify(VerifyRequest $request, UserService $userService): RedirectResponse
    {
        $userService->resendConfirmation($this->pendingUser($request), $request->validated('email'));

        return redirect()->route('verify')
            ->with('success', __('users.confirm_code_success_sent'));
    }

    /**
     * Confirm registration
     */
    public function confirm(string $token): RedirectResponse
    {
        // Только ожидающий аккаунт: старая ссылка из письма после одобрения админом
        // и повышения ставила бы модератору уровень пользователя
        $user = User::query()
            ->where('level', User::PENDED)
            ->where('confirm_token', $token)
            ->first();
        if (! $user) {
            abort(404, __('users.confirm_code_invalid'));
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
    public function setting(#[CurrentUser] User $user): View
    {
        $setting['themes'] = getAvailableThemes();
        $setting['languages'] = Locale::available();
        $setting['timezones'] = range(-12, 14);

        return view('users/settings', compact('user', 'setting'));
    }

    /**
     * Сохранение настроек
     */
    public function updateSetting(SettingsRequest $request, UserService $userService, #[CurrentUser] User $user): RedirectResponse
    {
        $userService->saveSettings($user, $request->settings(), $request);

        return redirect('settings')
            ->with('success', __('users.settings_success_changed'));
    }

    /**
     * Проверка доступности логина
     */
    public function checkLogin(Request $request): JsonResponse
    {
        if (! $request->ajax()) {
            return response()->json(['success' => false, 'message' => __('validator.not_ajax')], 400);
        }

        $request->validate(['login' => UserRules::login()], UserRules::messages());

        return response()->json(['success' => true]);
    }

    /**
     * Поиск пользователей для упоминаний
     */
    public function searchUsers(Request $request): JsonResponse
    {
        $query = $request->input('query');

        if (! is_string($query) || mb_strlen($query) < 2) {
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

    /**
     * Пользователь, ждущий подтверждения почты
     */
    private function pendingUser(Request $request): User
    {
        if (! $user = $request->user()) {
            abort(403, __('main.not_authorized'));
        }

        if (! UserService::isEmailConfirm()) {
            abort(200, __('users.confirm_registration_disabled'));
        }

        if ($user->level !== User::PENDED) {
            abort(403, __('users.profile_not_confirmation'));
        }

        return $user;
    }
}
