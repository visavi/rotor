<?php

declare(strict_types=1);

namespace App\Observers;

use App\Models\User;
use Illuminate\Support\Facades\Cache;

class UserObserver
{
    public function saved(User $user): void
    {
        if ($user->wasChanged(['name', 'login'])) {
            Cache::forget('users');
        }
    }

    public function deleted(User $user): void
    {
        if (filled($user->name)) {
            Cache::forget('users');
        }
    }
}
