<?php

declare(strict_types=1);

namespace App\Http\Controllers\Admin;

use App\Http\Requests\Admin\RuleRequest;
use App\Models\Rule;
use Illuminate\Http\RedirectResponse;
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
    public function edit(): View
    {
        $rules = Rule::query()->firstOrNew();

        return view('admin/rules/edit', compact('rules'));
    }

    /**
     * Сохранение правил
     */
    public function update(RuleRequest $request): RedirectResponse
    {
        Rule::query()->firstOrNew()->fill([
            'text'       => $request->validated('msg'),
            'created_at' => now(),
        ])->save();

        return redirect('admin/rules')
            ->with('success', __('admin.rules.rules_success_saved'));
    }
}
