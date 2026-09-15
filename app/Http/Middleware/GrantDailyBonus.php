<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class GrantDailyBonus
{
    /**
     * Начисляет ежедневный бонус и показывает уведомление о нём
     */
    public function handle(Request $request, Closure $next)
    {
        // Фоновый запрос клиента не означает, что человек за экраном:
        // уведомление ушло бы во флеш-сессию, которую никто не увидит,
        // а повторно за сутки бонус уже не начислится
        if ($request->ajax() || $request->expectsJson()) {
            return $next($request);
        }

        $bonus = auth()->user()?->gettingBonus();

        if (! $bonus) {
            return $next($request);
        }

        // Пишем до контроллера, чтобы уведомление попало в текущий рендер
        $this->addSuccess($bonus);

        $response = $next($request);

        // Контроллер мог положить своё уведомление через redirect()->with('success'),
        // перезаписав ключ целиком — возвращаем бонус обратно
        $this->addSuccess($bonus);

        return $response;
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
