<?php

declare(strict_types=1);

namespace App\Http\Controllers\Admin;

use App\Classes\Validator;
use App\Models\Advert;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class AdvertController extends AdminController
{
    /**
     * Главная страница
     */
    public function index(): View
    {
        $records = Advert::query()
            ->where('deleted_at', '>', SITETIME)
            ->orderByDesc('deleted_at')
            ->with('user')
            ->paginate(setting('rekuserpost'));

        return view('admin/adverts/index', compact('records'));
    }

    /**
     * Редактирование ссылки
     *
     *
     * @return View|RedirectResponse
     */
    public function edit(int $id, Request $request, Validator $validator)
    {
        $page = int($request->input('page', 1));
        $link = Advert::query()->find($id);

        if (! $link) {
            abort(404, __('main.record_not_found'));
        }

        if ($request->isMethod('post')) {
            $site = $request->input('site');
            $name = $request->input('name');
            $color = $request->input('color');
            $bold = empty($request->input('bold')) ? 0 : 1;

            $validator->equal($request->input('_token'), csrf_token(), __('validator.token'))
                ->regex($site, '|^https?://([а-яa-z0-9_\-\.])+(\.([а-яa-z0-9\/\-?_=#])+)+$|iu', ['site' => __('validator.url')])
                ->length($site, 5, 50, ['site' => __('validator.url_text')])
                ->length($name, 5, 35, ['name' => __('validator.text')])
                ->regex($color, '|^#+[A-f0-9]{6}$|', ['color' => __('validator.color')], false);

            if ($validator->isValid()) {
                $link->update([
                    'site'  => $site,
                    'name'  => $name,
                    'color' => $color,
                    'bold'  => $bold,
                ]);

                clearCache('adverts');
                setFlash('success', __('main.record_changed_success'));

                return redirect('admin/adverts?page=' . $page);
            }

            setInput($request->all());
            setFlash('danger', $validator->getErrors());
        }

        return view('admin/adverts/edit', compact('link', 'page'));
    }

    /**
     * Удаление записей
     */
    public function delete(Request $request, Validator $validator): RedirectResponse
    {
        $page = int($request->input('page', 1));
        $del = intar($request->input('del'));

        $validator->equal($request->input('_token'), csrf_token(), __('validator.token'))
            ->true($del, __('validator.deletion'));

        if ($validator->isValid()) {
            Advert::query()->whereIn('id', $del)->delete();

            clearCache('adverts');
            setFlash('success', __('main.records_deleted_success'));
        } else {
            setFlash('danger', $validator->getErrors());
        }

        return redirect('admin/adverts?page=' . $page);
    }
}
