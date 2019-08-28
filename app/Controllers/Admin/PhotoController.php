<?php

declare(strict_types=1);

namespace App\Controllers\Admin;

use App\Classes\Validator;
use App\Models\Photo;
use App\Models\User;
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
        $total = Photo::query()->count();
        $page  = paginate(setting('fotolist'), $total);

        $photos = Photo::query()
            ->orderBy('created_at', 'desc')
            ->offset($page->offset)
            ->limit($page->limit)
            ->with('user')
            ->get();

        return view('admin/photos/index', compact('photos', 'page'));
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
            abort(404, 'Данной фотографии не существует!');
        }

        if ($request->isMethod('post')) {
            $token  = check($request->input('token'));
            $title  = check($request->input('title'));
            $text   = check($request->input('text'));
            $closed = empty($request->input('closed')) ? 0 : 1;

            $validator->equal($token, $_SESSION['token'], trans('validator.token'))
                ->length($title, 5, 50, ['title' => trans('validator.text')])
                ->length($text, 0, 1000, ['text' => 'Слишком длинное описание!']);

            if ($validator->isValid()) {

                $text = antimat($text);

                $photo->update([
                    'title'  => $title,
                    'text'   => $text,
                    'closed' => $closed
                ]);

                clearCache(['statphotos', 'recentphotos']);
                setFlash('success', 'Фотография успешно отредактирована!');
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
     * @throws \Exception
     */
    public function delete(int $id, Request $request, Validator $validator): void
    {
        if (! is_writable(UPLOADS . '/photos')) {
            abort('default', 'Директория c фотографиями недоступна для записи!');
        }

        $page  = int($request->input('page', 1));
        $token = check($request->input('token'));

        /** @var Photo $photo */
        $photo = Photo::query()->find($id);

        if (! $photo) {
            abort(404, 'Данной фотографии не существует!');
        }

        $validator->equal($token, $_SESSION['token'], trans('validator.token'));

        if ($validator->isValid()) {

            $photo->comments()->delete();
            $photo->delete();

            clearCache(['statphotos', 'recentphotos']);
            setFlash('success', 'Фотография успешно удалена!');
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

                setFlash('success', 'Комментарии успешно пересчитаны!');
                redirect('/admin/photos');
            } else {
                abort('default', trans('validator.token'));
            }
        } else {
            abort('default', 'Пересчитывать комментарии могут только суперадмины!');
        }
    }
}
