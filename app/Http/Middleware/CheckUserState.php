<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

class CheckUserState
{
    /**
     * Handle an incoming request.
     */
    public function handle(Request $request, Closure $next)
    {
        if (
            $request->routeIs('ipban')
            || (Route::has('install') && $request->is('install*'))
        ) {
            return $next($request);
        }

        if ($user = auth()->user()) {
            // Проверка бана
            if ($user->isBanned() && ! $request->routeIs('ban', 'rules', 'logout')) {
                return redirect('ban?user=' . $user->login);
            }

            // Проверка статуса pending
            if ($user->isPended() && ! $request->routeIs('verify', 'confirm', 'ban', 'logout', 'captcha')) {
                return redirect()->route('verify', ['user' => $user->login]);
            }

            // Обновление данных пользователя
            $user->updatePrivate();
            $bonus = $user->gettingBonus();

            if ($bonus) {
                // Пишем до контроллера, чтобы уведомление попало в текущий рендер
                $this->addSuccess($bonus);

                $response = $next($request);

                // Контроллер мог положить своё уведомление через redirect()->with('success'),
                // перезаписав ключ целиком — возвращаем бонус обратно
                $this->addSuccess($bonus);

                return $response;
            }
        }

        return $next($request);
    }

    /**
     * Добавляет уведомление, не затирая уже записанные
     */
    private function addSuccess(string $message): void
    {
        $messages = (array) session()->get('success', []);

        if (! in_array($message, $messages, true)) {
            session()->flash('success', [...$messages, $message]);
        }
    }
}
