<?php

declare(strict_types=1);

namespace App\Traits;

use App\Models\User;
use App\Services\MetrikaService;
use App\Support\Locale;
use Illuminate\Http\Request;

trait AuthenticatesToken
{
    /**
     * Авторизует владельца токена и отмечает визит
     */
    protected function authenticateToken(string $token, Request $request, MetrikaService $metrikaService): void
    {
        if (! $user = User::query()->where('apikey', $token)->first()) {
            abort(401, 'Unauthorized');
        }

        if ($user->level === User::BANNED) {
            abort(403, 'User banned');
        }

        auth()->setUser($user);

        // Язык профиля важнее Accept-Language, который поставила группа api
        Locale::apply($user->language);

        $metrikaService->saveVisit($user, $request);
    }
}
