<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

class MailController extends Controller
{
    /**
     * Старый адрес обратной связи
     *
     * Форма переехала в модуль Feedback. Маршрут остаётся ради ссылок на /mails
     * и route('mails.index') в старых темах из resources/custom/views: без него
     * такая тема падала бы на каждой странице
     *
     * @deprecated до 15.0
     */
    public function index(): RedirectResponse
    {
        abort_unless(Route::has('feedback.index'), 404);

        return redirect()->route('feedback.index', status: 301);
    }

    /**
     * Отписка от рассылки
     */
    public function unsubscribe(Request $request): RedirectResponse
    {
        $key = $request->input('key');

        if (! $key) {
            abort(200, __('mails.token_missing'));
        }

        $user = User::query()->where('subscribe', $key)->first();

        if (! $user) {
            abort(200, __('mails.token_expired'));
        }

        $user->subscribe = null;
        $user->save();

        return redirect('/')
            ->with('success', __('mails.success_unsubscribed'));
    }
}
