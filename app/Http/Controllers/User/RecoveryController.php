<?php

declare(strict_types=1);

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use App\Http\Requests\User\RecoveryRequest;
use App\Models\PasswordReset;
use App\Models\User;
use App\Services\MailService;
use App\Services\UserService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Illuminate\View\View;

class RecoveryController extends Controller
{
    /**
     * Восстановление пароля
     */
    public function recovery(): View|RedirectResponse
    {
        if (auth()->check()) {
            return redirect('/')->with('danger', __('main.already_authorized'));
        }

        return view('users/recovery');
    }

    /**
     * Заявка на восстановление, ссылка уходит на почту
     */
    public function sendRecovery(RecoveryRequest $request, UserService $userService): RedirectResponse
    {
        $user = $request->recoveryUser();

        $userService->requestRecovery($user);

        return redirect('/')->with('success', __('mails.recovery_instructions', ['email' => hideMail((string) $user->email)]));
    }

    /**
     * Восстановление пароля
     */
    public function restore(string $token, MailService $mail): View|RedirectResponse
    {
        if (auth()->check()) {
            return redirect('/')->with('danger', __('main.already_authorized'));
        }

        PasswordReset::query()
            ->where('created_at', '<', now()->subHour())
            ->delete();

        $reset = PasswordReset::query()->where('token', $token)->first();
        if (! $reset) {
            abort(404, __('mails.token_invalid'));
        }

        $user = User::query()->where('email', $reset->email)->first();
        if (! $user) {
            abort(404, __('mails.password_not_recovery'));
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
        $mail->queue('mailer.restore', $data);

        Auth::login($user, true);

        $reset->delete();
        PasswordReset::query()
            ->where('created_at', '<', now()->subHour())
            ->delete();

        return redirect('/')
            ->with('success', __('mails.success_recovery', ['password' => $password]));
    }
}
