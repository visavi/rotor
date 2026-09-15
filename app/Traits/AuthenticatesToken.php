<?php

declare(strict_types=1);

namespace App\Traits;

use App\Http\Middleware\SetLocale;
use App\Models\User;
use App\Services\MetrikaService;

trait AuthenticatesToken
{
    /**
     * Авторизует владельца токена и отмечает визит
     */
    protected function authenticateToken(string $token): void
    {
        if (! $user = User::query()->where('apikey', $token)->first()) {
            abort(401, 'Unauthorized');
        }

        if ($user->level === User::BANNED) {
            abort(403, 'User banned');
        }

        auth()->setUser($user);

        // Глобальный SetLocale отработал до авторизации по токену и видел гостя,
        // поэтому язык профиля проставляется здесь
        SetLocale::apply($user->language);

        (new MetrikaService())->saveVisit($user);
    }
}
