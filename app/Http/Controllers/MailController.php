<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use Illuminate\Http\RedirectResponse;
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
}
