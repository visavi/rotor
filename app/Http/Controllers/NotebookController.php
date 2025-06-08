<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Classes\Validator;
use App\Models\Notebook;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class NotebookController extends Controller
{
    private $note;

    /**
     * Конструктор
     */
    public function __construct()
    {
        $this->middleware('check.user');

        $this->middleware(function ($request, $next) {
            $user = getUser();

            $this->note = Notebook::query()
                ->where('user_id', $user->id)
                ->firstOrNew(['user_id' => $user->id]);

            return $next($request);
        });
    }

    /**
     * Главная страница
     */
    public function index(): View
    {
        return view('notebooks/index', ['note' => $this->note]);
    }

    /**
     * Редактирование
     */
    public function edit(Request $request, Validator $validator): View|RedirectResponse
    {
        if ($request->isMethod('post')) {
            $msg = $request->input('msg');

            $validator
                ->equal($request->input('_token'), csrf_token(), ['msg' => __('validator.token')])
                ->length($msg, 0, 10000, ['msg' => __('validator.text_long')], false);

            if ($validator->isValid()) {
                $this->note->fill([
                    'text'       => $msg,
                    'created_at' => SITETIME,
                ])->save();

                setFlash('success', __('main.record_saved_success'));
            } else {
                setInput($request->all());
                setFlash('danger', $validator->getErrors());
            }

            return redirect('notebooks');
        }

        return view('notebooks/edit', ['note' => $this->note]);
    }
}
