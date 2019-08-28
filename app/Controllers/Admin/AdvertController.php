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
        $total = Advert::query()->where('deleted_at', '>', SITETIME)->count();
        $page = paginate(setting('rekuserpost'), $total);

        $records = Advert::query()
            ->where('deleted_at', '>', SITETIME)
            ->limit($page->limit)
            ->offset($page->offset)
            ->orderBy('deleted_at', 'desc')
            ->with('user')
            ->get();

        return view('admin/adverts/index', compact('records', 'page'));
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
            abort(404, trans('main.record_not_found'));
        }

        if ($request->isMethod('post')) {
            $token = check($request->input('token'));
            $site  = check($request->input('site'));
            $name  = check($request->input('name'));
            $color = check($request->input('color'));
            $bold  = empty($request->input('bold')) ? 0 : 1;

            $validator->equal($token, $_SESSION['token'], trans('validator.token'))
                ->regex($site, '|^https?://([а-яa-z0-9_\-\.])+(\.([а-яa-z0-9\/\-?_=#])+)+$|iu', ['site' => trans('validator.url')])
                ->length($site, 5, 50, ['site' => trans('validator.url_text')])
                ->length($name, 5, 35, ['name' => trans('validator.title')])
                ->regex($color, '|^#+[A-f0-9]{6}$|', ['color' => trans('validator.color')], false);

            if ($validator->isValid()) {

                $link->update([
                    'site'  => $site,
                    'name'  => $name,
                    'color' => $color,
                    'bold'  => $bold,
                ]);

                saveAdvertUser();

                setFlash('success', trans('main.record_changed_success'));
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

        $validator->equal($token, $_SESSION['token'], trans('validator.token'))
            ->true($del, trans('validator.deletion'));

        if ($validator->isValid()) {
            Advert::query()->whereIn('id', $del)->delete();

            saveAdvertUser();

            setFlash('success', trans('main.records_deleted_success'));
        } else {
            setFlash('danger', $validator->getErrors());
        }

        redirect('/admin/adverts?page=' . $page);
    }
}
