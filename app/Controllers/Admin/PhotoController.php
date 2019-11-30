<?php

declare(strict_types=1);

namespace App\Controllers\Admin;

use App\Classes\Validator;
use App\Models\Photo;
use App\Models\User;
use Exception;
use Illuminate\Http\Request;

class PhotoController extends AdminController
{
    /**
     * Главная страница
     *
     * @return string
     */
    public function index(): string
    {
        $photos = Photo::query()
            ->orderByDesc('created_at')
            ->with('user')
            ->paginate(setting('fotolist'));

        return view('admin/photos/index', compact('photos'));
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
        $page  = int($request->input('page', 1));
        $photo = Photo::query()->find($id);

        if (! $photo) {
            abort(404, __('photos.photo_not_exist'));
        }

        if ($request->isMethod('post')) {
            $token  = check($request->input('token'));
            $title  = check($request->input('title'));
            $text   = check($request->input('text'));
            $closed = empty($request->input('closed')) ? 0 : 1;

            $validator->equal($token, $_SESSION['token'], __('validator.token'))
                ->length($title, 5, 50, ['title' => __('validator.text')])
                ->length($text, 0, 1000, ['text' => __('validator.text_long')]);

            if ($validator->isValid()) {
                $text = antimat($text);

                $photo->update([
                    'title'  => $title,
                    'text'   => $text,
                    'closed' => $closed
                ]);

                clearCache(['statPhotos', 'recentPhotos']);
                setFlash('success', __('photos.photo_success_edited'));
                redirect('/admin/photos?page=' . $page);
            } else {
                setInput($request->all());
                setFlash('danger', $validator->getErrors());
            }
        }

        return view('admin/photos/edit', compact('photo', 'page'));
    }

    /**
     * Удаление записей
     *
     * @param int       $id
     * @param Request   $request
     * @param Validator $validator
     * @return void
     * @throws Exception
     */
    public function delete(int $id, Request $request, Validator $validator): void
    {
        if (! is_writable(UPLOADS . '/photos')) {
            abort('default', __('main.directory_not_writable'));
        }

        $page  = int($request->input('page', 1));
        $token = check($request->input('token'));

        /** @var Photo $photo */
        $photo = Photo::query()->find($id);

        if (! $photo) {
            abort(404, __('photos.photo_not_exist'));
        }

        $validator->equal($token, $_SESSION['token'], __('validator.token'));

        if ($validator->isValid()) {
            $photo->comments()->delete();
            $photo->delete();

            clearCache(['statPhotos', 'recentPhotos']);
            setFlash('success', __('photos.photo_success_deleted'));
        } else {
            setFlash('danger', $validator->getErrors());
        }

        redirect('/admin/photos?page=' . $page);
    }

    /**
     * Пересчет комментариев
     *
     * @param Request $request
     * @return void
     */
    public function restatement(Request $request): void
    {
        $token = check($request->input('token'));

        if (isAdmin(User::BOSS)) {
            if ($token === $_SESSION['token']) {
                restatement('photos');

                setFlash('success', __('main.success_recounted'));
                redirect('/admin/photos');
            } else {
                abort('default', __('validator.token'));
            }
        } else {
            abort('default', __('main.page_only_owner'));
        }
    }
}
