<?php

declare(strict_types=1);

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use App\Models\PasswordReset;
use App\Models\User;
use App\Services\UserService;
use App\Support\Validator;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Illuminate\View\View;

class RecoveryController extends Controller
{
    /**
     * Восстановление пароля
     */
    public function recovery(Request $request, Validator $validator, UserService $userService): View|RedirectResponse
    {
        if (getUser()) {
            return redirect('/')->with('danger', __('main.already_authorized'));
        }

        if ($request->isMethod('post')) {
            $user = getUserByLoginOrEmail($request->input('user'));
            if (! $user) {
                abort(200, __('validator.user'));
            }

            $validator->true(captchaVerify(), ['protect' => __('validator.captcha')]);

            if ($userService->hasPendingRecovery($user)) {
                $validator->addError(['user' => __('mails.password_recovery_time')]);
            }

            if ($validator->isValid()) {
                $userService->requestRecovery($user);

                return redirect('/')->with('success', __('mails.recovery_instructions', ['email' => hideMail($user->email)]));
            }

            return redirect('recovery')
                ->withInput()
                ->withErrors($validator->getErrors());
        }

        return view('users/recovery');
    }

    /**
     * Восстановление пароля
     */
    public function restore(string $token): View|RedirectResponse
    {
        if (getUser()) {
            return redirect('/')->with('danger', __('mails.already_authorized'));
        }

        PasswordReset::query()
            ->where('created_at', '<', now()->subHour())
            ->delete();

        $reset = PasswordReset::query()->where('token', $token)->first();
        if (! $reset) {
            abort(200, __('mails.token_invalid'));
        }

        $user = User::query()->where('email', $reset->email)->first();
        if (! $user) {
            abort(200, __('mails.password_not_recovery'));
        }

        $password = Str::random();
        $user->update(['password' => Hash::make($password)]);

        // Восстановление пароля на email
        $subject = 'Новый пароль на ' . setting('title');
        $data = [
            'to'       => $user->email,
            'subject'  => $subject,
            'username' => $user->getName(),
            'login'    => $user->login,
            'password' => $password,
        ];
        sendMail('mailer.restore', $data);

        Auth::login($user, true);

        $reset->delete();
        PasswordReset::query()
            ->where('created_at', '<', now()->subHour())
            ->delete();

        return redirect('/')
            ->with('success', __('mails.success_recovery', ['password' => $password]));
    }
}
