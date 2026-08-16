<?php

declare(strict_types=1);

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use App\Models\BlackList;
use App\Models\EmailChange;
use App\Models\User;
use App\Services\UserService;
use App\Support\Validator;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\View\View;

class AccountController extends Controller
{
    /**
     * User data
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
     */
    public function changeMail(Request $request, Validator $validator, UserService $userService): RedirectResponse
    {
        if (! $user = getUser()) {
            abort(403, __('main.not_authorized'));
        }

        $email = strtolower((string) $request->input('email'));

        $userService->validateEmailChange($validator, $user, $email, $request->input('password'));

        if (! $validator->isValid()) {
            return redirect('accounts')
                ->withInput()
                ->withErrors($validator->getErrors());
        }

        $userService->requestEmailChange($user, $email);

        return redirect('accounts')
            ->with('success', __('users.confirm_success_sent'));
    }

    /**
     * Email change
     */
    public function editMail(string $token, Validator $validator): RedirectResponse
    {
        if (! $user = getUser()) {
            abort(403, __('main.not_authorized'));
        }

        EmailChange::query()
            ->where('created_at', '<', now()->subHour())
            ->delete();

        $emailChange = EmailChange::query()
            ->where('token', $token)
            ->where('user_id', $user->id)
            ->first();

        $validator->notEmpty($emailChange, __('users.changed_code_not_found'));

        if ($emailChange) {
            $isEmailExists = User::query()->where('email', $emailChange->email)->exists();
            $validator->false($isEmailExists, __('users.email_already_exists'));

            $validator->false(BlackList::isBlacklisted('email', $emailChange->email), __('users.email_is_blacklisted'));
        }

        if ($validator->isValid()) {
            $user->update([
                'email' => $emailChange->email,
            ]);

            $emailChange->delete();

            $flash = ['success', __('users.email_success_changed')];
        } else {
            $flash = ['danger', $validator->getErrors()];
        }

        return redirect()->route('accounts.account')
            ->with(...$flash);
    }

    /**
     * Status change
     */
    public function editStatus(Request $request, Validator $validator): RedirectResponse
    {
        if (! $user = getUser()) {
            abort(403, __('main.not_authorized'));
        }

        $status = $request->input('status');
        $status = ! empty($status) ? $status : null;
        $cost = $status ? setting('editstatusmoney') : 0;

        $validator
            ->empty($user->ban, ['status' => __('users.status_changed_not_ban')])
            ->notEqual($status, $user->status, ['status' => __('users.status_different')])
            ->gte($user->point, setting('editstatuspoint'), ['status' => __('users.status_points')])
            ->gte($user->money, $cost, ['status' => __('users.status_moneys')])
            ->length($status, 3, 25, ['status' => __('users.status_short_or_long')], false);

        if (! $validator->isValid()) {
            return redirect('accounts')
                ->withInput()
                ->withErrors($validator->getErrors());
        }

        $user->update([
            'status' => $status,
            'money'  => DB::raw('money - ' . $cost),
        ]);

        clearCache('status');

        return redirect('accounts')
            ->with('success', __('users.status_success_changed'));
    }

    /**
     * Color change
     */
    public function editColor(Request $request, Validator $validator): RedirectResponse
    {
        if (! $user = getUser()) {
            abort(403, __('main.not_authorized'));
        }

        $color = $request->input('color');
        $color = ! empty($color) ? $color : null;
        $cost = $color ? setting('editcolormoney') : 0;

        $validator
            ->notEqual($color, $user->color, ['color' => __('users.color_different')])
            ->gte($user->point, setting('editcolorpoint'), ['color' => __('users.color_points')])
            ->gte($user->money, $cost, ['color' => __('users.color_moneys')])
            ->regex($color, '|^#+[A-f0-9]{6}$|', ['color' => __('validator.color')], false);

        if (! $validator->isValid()) {
            return redirect('accounts')
                ->withInput()
                ->withErrors($validator->getErrors());
        }

        $user->update([
            'color' => $color,
            'money' => DB::raw('money - ' . $cost),
        ]);

        return redirect('accounts')
            ->with('success', __('users.color_success_changed'));
    }

    /**
     * Password change
     */
    public function editPassword(Request $request, Validator $validator, UserService $userService): RedirectResponse
    {
        if (! $user = getUser()) {
            abort(403, __('main.not_authorized'));
        }

        $newPassword = $request->input('new_password');

        $userService->validatePassword(
            $validator,
            $user,
            $request->input('old_password'),
            $newPassword,
            $request->input('confirm_password'),
        );

        if ($validator->isValid()) {
            $userService->changePassword($user, $newPassword);

            $request->session()->regenerate();

            return redirect('/')->with('success', __('users.password_success_changed'));
        }

        return redirect('accounts')
            ->withErrors($validator->getErrors())
            ->withInput();
    }

    /**
     * Key generation
     */
    public function apikey(Request $request): RedirectResponse
    {
        if (! $user = getUser()) {
            abort(403, __('main.not_authorized'));
        }

        $apiKey = Str::random(32);
        $message = __('users.token_success_changed');

        if ($request->input('action') === 'create') {
            $message = __('users.token_success_created');
        }

        if ($request->input('action') === 'delete') {
            $apiKey = '';
            $message = __('users.token_success_deleted');
        }

        $user->update([
            'apikey' => $apiKey,
        ]);

        return redirect('accounts')
            ->with('success', $message);
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
            $isLoginExists = User::query()
                ->where('login', $login)
                ->exists();

            $isLoginBlacklisted = Blacklist::query()
                ->where('type', 'login')
                ->where('value', strtolower($login))
                ->exists();

            $validator
                ->false($isLoginExists, __('users.login_already_exists'))
                ->false($isLoginBlacklisted, __('users.login_is_blacklisted'));
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
