<?php

declare(strict_types=1);

namespace App\Observers;

use App\Models\User;

class UserObserver
{
    public function saved(User $user): void
    {
        if ($user->wasChanged(['name', 'login'])) {
            clearCache('users');
        }
    }

    public function deleted(User $user): void
    {
        if (filled($user->name)) {
            clearCache('users');
        }
    }
}
