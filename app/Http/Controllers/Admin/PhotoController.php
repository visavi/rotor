<?php

declare(strict_types=1);

namespace App\Http\Controllers\Admin;

use App\Classes\Validator;
use App\Models\Photo;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class PhotoController extends AdminController
{
    /**
     * Главная страница
     */
    public function index(): View
    {
        $photos = Photo::query()
            ->orderByDesc('created_at')
            ->with('user', 'files')
            ->paginate(setting('fotolist'));

        return view('admin/photos/index', compact('photos'));
    }

    /**
     * Редактирование ссылки
     */
    public function edit(int $id, Request $request, Validator $validator): View|RedirectResponse
    {
        $page = int($request->input('page', 1));
        $photo = Photo::query()->find($id);

        if (! $photo) {
            abort(404, __('photos.photo_not_exist'));
        }

        if ($request->isMethod('post')) {
            $title = $request->input('title');
            $text = $request->input('text');
            $closed = empty($request->input('closed')) ? 0 : 1;

            $validator
                ->length($title, setting('photo_title_min'), setting('photo_title_max'), ['title' => __('validator.text')])
                ->length($text, setting('photo_text_min'), setting('photo_text_max'), ['text' => __('validator.text_long')]);

            if ($validator->isValid()) {
                $text = antimat($text);

                $photo->update([
                    'title'  => $title,
                    'text'   => $text,
                    'closed' => $closed,
                ]);

                clearCache(['statPhotos', 'recentPhotos', 'PhotoFeed']);
                setFlash('success', __('photos.photo_success_edited'));

                return redirect()->route('admin.photos.index', ['page' => $page]);
            }

            setInput($request->all());
            setFlash('danger', $validator->getErrors());
        }

        return view('admin/photos/edit', compact('photo', 'page'));
    }

    /**
     * Удаление записей
     */
    public function delete(int $id, Request $request, Validator $validator): RedirectResponse
    {
        if (! is_writable(public_path('uploads/photos'))) {
            abort(200, __('main.directory_not_writable'));
        }

        $page = int($request->input('page', 1));

        $photo = Photo::query()->find($id);

        if (! $photo) {
            abort(404, __('photos.photo_not_exist'));
        }

        $validator->equal($request->input('_token'), csrf_token(), __('validator.token'));

        if ($validator->isValid()) {
            $photo->delete();

            clearCache(['statPhotos', 'recentPhotos', 'PhotoFeed']);
            setFlash('success', __('photos.photo_success_deleted'));
        } else {
            setFlash('danger', $validator->getErrors());
        }

        return redirect()->route('admin.photos.index', ['page' => $page]);
    }

    /**
     * Пересчет комментариев
     */
    public function restatement(Request $request): RedirectResponse
    {
        if (! isAdmin(User::BOSS)) {
            abort(200, __('main.page_only_owner'));
        }

        if ($request->input('_token') === csrf_token()) {
            restatement('photos');

            setFlash('success', __('main.success_recounted'));
        } else {
            setFlash('danger', __('validator.token'));
        }

        return redirect()->route('admin.photos.index');
    }
}
