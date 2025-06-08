<?php

declare(strict_types=1);

namespace App\Http\Controllers\Admin;

use App\Classes\Validator;
use App\Models\Rule;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class RuleController extends AdminController
{
    /**
     * Главная страница
     */
    public function index(): View
    {
        $rules = Rule::query()->first();

        $replace = [
            '%SITENAME%' => setting('title'),
        ];

        if ($rules) {
            $rules->text = str_replace(array_keys($replace), $replace, $rules->text);
        }

        return view('admin/rules/index', compact('rules'));
    }

    /**
     * Редактирование правил
     */
    public function edit(Request $request, Validator $validator): View|RedirectResponse
    {
        $rules = Rule::query()->firstOrNew();

        if ($request->isMethod('post')) {
            $msg = $request->input('msg');

            $validator
                ->equal($request->input('_token'), csrf_token(), __('validator.token'))
                ->notEmpty($msg, ['msg' => __('admin.rules.rules_empty')]);

            if ($validator->isValid()) {
                $rules->fill([
                    'text'       => $msg,
                    'created_at' => SITETIME,
                ])->save();

                setFlash('success', __('admin.rules.rules_success_saved'));

                return redirect('admin/rules');
            }

            setInput($request->all());
            setFlash('danger', $validator->getErrors());
        }

        return view('admin/rules/edit', compact('rules'));
    }
}
