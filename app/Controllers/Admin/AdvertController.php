<?php

declare(strict_types=1);

namespace App\Controllers\Admin;

use App\Classes\Validator;
use App\Models\Advert;
use Illuminate\Http\Request;

class AdvertController extends AdminController
{
    /**
     * Главная страница
     *
     * @return string
     */
    public function index(): string
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
     * @param int       $id
     * @param Request   $request
     * @param Validator $validator
     * @return string
     */
    public function edit(int $id, Request $request, Validator $validator): string
    {
        $page = int($request->input('page', 1));
        $link = Advert::query()->find($id);

        if (! $link) {
            abort(404, __('main.record_not_found'));
        }

        if ($request->isMethod('post')) {
            $token = check($request->input('token'));
            $site  = check($request->input('site'));
            $name  = check($request->input('name'));
            $color = check($request->input('color'));
            $bold  = empty($request->input('bold')) ? 0 : 1;

            $validator->equal($token, $_SESSION['token'], __('validator.token'))
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
                redirect('/admin/adverts?page=' . $page);
            } else {
                setInput($request->all());
                setFlash('danger', $validator->getErrors());
            }
        }

        return view('admin/adverts/edit', compact('link', 'page'));
    }

    /**
     * Удаление записей
     *
     * @param Request   $request
     * @param Validator $validator
     * @return void
     */
    public function delete(Request $request, Validator $validator): void
    {
        $page  = int($request->input('page', 1));
        $token = check($request->input('token'));
        $del   = intar($request->input('del'));

        $validator->equal($token, $_SESSION['token'], __('validator.token'))
            ->true($del, __('validator.deletion'));

        if ($validator->isValid()) {
            Advert::query()->whereIn('id', $del)->delete();

            clearCache('adverts');

            setFlash('success', __('main.records_deleted_success'));
        } else {
            setFlash('danger', $validator->getErrors());
        }

        redirect('/admin/adverts?page=' . $page);
    }
}
