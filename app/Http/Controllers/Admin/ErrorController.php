<?php

declare(strict_types=1);

namespace App\Http\Controllers\Admin;

use App\Classes\Validator;
use App\Models\Error;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ErrorController extends AdminController
{
    private array $lists;
    private int $code;

    /**
     * Конструктор
     */
    public function __construct(Request $request)
    {
        $this->code = int($request->input('code', 404));
        $this->lists = [
            401 => 401,
            403 => 403,
            404 => 404,
            405 => 405,
            419 => 419,
            429 => 429,
            500 => 500,
            503 => 503,
            666 => __('admin.errors.autobans'),
        ];

        if (! isset($this->lists[$this->code])) {
            abort(404, __('admin.errors.logs_not_exist'));
        }
    }

    /**
     * Главная страница
     */
    public function index(): View
    {
        $lists = $this->lists;
        $code = $this->code;

        $logs = Error::query()
            ->where('code', $code)
            ->orderByDesc('created_at')
            ->with('user')
            ->paginate(setting('loglist'))
            ->appends(['code' => $code]);

        return view('admin/errors/index', compact('logs', 'code', 'lists'));
    }

    /**
     * Очистка логов
     */
    public function clear(Request $request, Validator $validator): RedirectResponse
    {
        $validator
            ->equal($request->input('_token'), csrf_token(), __('validator.token'))
            ->true(isAdmin(User::BOSS), __('main.page_only_admins'));

        if ($validator->isValid()) {
            Error::query()->truncate();

            setFlash('success', __('admin.errors.success_cleared'));
        } else {
            setFlash('danger', $validator->getErrors());
        }

        return redirect('admin/errors');
    }
}
