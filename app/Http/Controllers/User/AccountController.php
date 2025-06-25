<?php

declare(strict_types=1);

namespace App\Http\Controllers\User;

use App\Classes\Validator;
use App\Http\Controllers\Controller;
use App\Models\BlackList;
use App\Models\EmailChange;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
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
    public function changeMail(Request $request, Validator $validator): RedirectResponse
    {
        if (! $user = getUser()) {
            abort(403, __('main.not_authorized'));
        }

        $email = strtolower((string) $request->input('email'));
        $password = $request->input('password');

        $validator->equal($request->input('_token'), csrf_token(), __('validator.token'))
            ->notEqual($email, $user->email, ['email' => __('users.email_different')])
            ->email($email, ['email' => __('validator.email')])
            ->true(Hash::check($password, $user->password), ['password' => __('users.password_different')]);

        $isEMailExists = User::query()->where('email', $email)->exists();
        $validator->empty($isEMailExists, ['email' => __('users.email_already_exists')]);

        $isEmailBlacklisted = BlackList::query()->where('type', 'email')->where('value', $email)->exists();
        $validator->empty($isEmailBlacklisted, ['email' => __('users.email_is_blacklisted')]);

        EmailChange::query()
            ->where('created_at', '<', now()->subHour())
            ->delete();

        $emailChange = EmailChange::query()->where('user_id', $user->id)->first();
        $validator->empty($emailChange, __('users.confirm_already_sent'));

        if ($validator->isValid()) {
            $token = Str::random(32);
            $changeUrl = route('accounts.edit-mail', ['token' => $token]);

            $subject = 'Изменение email на ' . setting('title');
            $data = [
                'to'        => $email,
                'subject'   => $subject,
                'username'  => $user->getName(),
                'changeUrl' => $changeUrl,
            ];

            sendMail('mailer.change_mail', $data);

            EmailChange::query()->create([
                'user_id'    => $user->id,
                'email'      => $email,
                'token'      => $token,
                'created_at' => now(),
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
            $isEMailExists = User::query()->where('email', $emailChange->email)->exists();
            $validator->empty($isEMailExists, __('users.email_already_exists'));

            $isEmailBlacklisted = BlackList::query()->where('type', 'email')->where('value', $emailChange->email)->exists();
            $validator->empty($isEmailBlacklisted, __('users.email_is_blacklisted'));
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

        $validator->equal($request->input('_token'), csrf_token(), __('validator.token'))
            ->empty($user->ban, ['status' => __('users.status_changed_not_ban')])
            ->notEqual($status, $user->status, ['status' => __('users.status_different')])
            ->gte($user->point, setting('editstatuspoint'), ['status' => __('users.status_points')])
            ->gte($user->money, $cost, ['status' => __('users.status_moneys')])
            ->length($status, 3, 25, ['status' => __('users.status_short_or_long')], false);

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
     */
    public function editColor(Request $request, Validator $validator): RedirectResponse
    {
        if (! $user = getUser()) {
            abort(403, __('main.not_authorized'));
        }

        $color = $request->input('color');
        $color = ! empty($color) ? $color : null;
        $cost = $color ? setting('editcolormoney') : 0;

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
     */
    public function editPassword(Request $request, Validator $validator): RedirectResponse
    {
        if (! $user = getUser()) {
            abort(403, __('main.not_authorized'));
        }

        $newpass = $request->input('newpass');
        $newpass2 = $request->input('newpass2');
        $oldpass = $request->input('oldpass');

        $validator->equal($request->input('_token'), csrf_token(), __('validator.token'))
            ->true(password_verify((string) $oldpass, $user->password), ['oldpass' => __('users.password_different')])
            ->length($newpass, 6, 20, ['newpass' => __('users.password_length_requirements')])
            ->notEqual($user->login, $newpass, ['newpass' => __('users.login_different')])
            ->equal($newpass, $newpass2, ['newpass2' => __('users.passwords_different')]);

        if (ctype_digit($newpass)) {
            $validator->addError(['newpass' => __('users.field_characters_requirements')]);
        }

        if ($validator->isValid()) {
            $user->update([
                'password' => Hash::make($newpass),
            ]);

            $request->session()->regenerate();

            $subject = 'Изменение пароля на ' . setting('title');
            $message = 'Здравствуйте, ' . e($user->getName()) . '<br>Вами была произведена операция по изменению пароля<br><br><b>Ваш новый пароль: ' . $newpass . '</b><br>Сохраните его в надежном месте<br><br>Данные инициализации:<br>IP: ' . getIp() . '<br>Браузер: ' . getBrowser() . '<br>Время: ' . date('j.m.y / H:i', SITETIME);

            $data = [
                'to'      => $user->email,
                'subject' => $subject,
                'text'    => $message,
            ];

            sendMail('mailer.default', $data);

            setFlash('success', __('users.password_success_changed'));

            return redirect('/');
        }

        setInput($request->all());
        setFlash('danger', $validator->getErrors());

        return redirect('accounts');
    }

    /**
     * Key generation
     */
    public function apikey(Request $request): RedirectResponse
    {
        if (! $user = getUser()) {
            abort(403, __('main.not_authorized'));
        }

        if ($request->input('_token') === csrf_token()) {
            $apiKey = md5($user->login . Str::random());
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

            setFlash('success', $message);
        } else {
            setFlash('danger', __('validator.token'));
        }

        return redirect('accounts');
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
