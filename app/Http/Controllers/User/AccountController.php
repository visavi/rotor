<?php

declare(strict_types=1);

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use App\Http\Requests\User\ColorRequest;
use App\Http\Requests\User\EmailChangeRequest;
use App\Http\Requests\User\PasswordRequest;
use App\Http\Requests\User\StatusRequest;
use App\Models\BlackList;
use App\Models\EmailChange;
use App\Models\User;
use App\Services\UserService;
use Illuminate\Container\Attributes\CurrentUser;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class AccountController extends Controller
{
    /**
     * Initialize email change
     */
    public function changeMail(EmailChangeRequest $request, UserService $userService, #[CurrentUser] User $user): RedirectResponse
    {
        $userService->requestEmailChange($user, $request->validated('email'));

        return redirect('settings#tab-account')
            ->with('success', __('users.confirm_success_sent'));
    }

    /**
     * Email change
     */
    public function editMail(string $token, #[CurrentUser] User $user): RedirectResponse
    {
        EmailChange::query()
            ->where('created_at', '<', now()->subHour())
            ->delete();

        $emailChange = EmailChange::query()
            ->where('token', $token)
            ->where('user_id', $user->id)
            ->first();

        $error = match (true) {
            ! $emailChange                                               => __('users.changed_code_not_found'),
            User::query()->where('email', $emailChange->email)->exists() => __('users.email_already_exists'),
            BlackList::isBlacklisted('email', $emailChange->email)       => __('users.email_is_blacklisted'),
            default                                                      => null,
        };

        if ($error || ! $emailChange) {
            return redirect('settings#tab-account')
                ->withErrors($error);
        }

        $user->update([
            'email' => $emailChange->email,
        ]);

        $emailChange->delete();

        return redirect('settings#tab-account')
            ->with('success', __('users.email_success_changed'));
    }

    /**
     * Status change
     */
    public function editStatus(StatusRequest $request): RedirectResponse
    {
        $request->apply();

        clearCache('status');

        return redirect('settings#tab-appearance')
            ->with('success', __('users.status_success_changed'));
    }

    /**
     * Color change
     */
    public function editColor(ColorRequest $request): RedirectResponse
    {
        $request->apply();

        return redirect('settings#tab-appearance')
            ->with('success', __('users.color_success_changed'));
    }

    /**
     * Password change
     */
    public function editPassword(PasswordRequest $request, UserService $userService, #[CurrentUser] User $user): RedirectResponse
    {
        $userService->changePassword($user, $request->validated('new_password'));

        $request->session()->regenerate();

        return redirect('/')->with('success', __('users.password_success_changed'));
    }

    /**
     * Key generation
     */
    public function apikey(Request $request, #[CurrentUser] User $user): RedirectResponse
    {
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

        return redirect('settings#tab-api')
            ->with('success', $message);
    }
}
