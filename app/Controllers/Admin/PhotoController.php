<?php

namespace App\Controllers\Admin;

use App\Classes\Request;
use App\Classes\Validator;
use App\Models\Photo;
use App\Models\User;

class PhotoController extends AdminController
{
    /**
     * Конструктор
     */
    public function __construct()
    {
        parent::__construct();

        if (!isAdmin(User::EDITOR)) {
            abort(403, 'Доступ запрещен!');
        }
    }

    /**
     * Главная страница
     *
     * @return string
     */
    public function index(): string
    {
        $total = Photo::count();
        $page  = paginate(setting('fotolist'), $total);

        $photos = Photo::orderBy('created_at', 'desc')
            ->offset($page->offset)
            ->limit($page->limit)
            ->with('user')
            ->get();

        return view('admin/photos/index', compact('photos', 'page'));
    }

    /**
     * Редактирование ссылки
     *
     * @param int $id
     * @return string
     */
    public function edit($id): string
    {
        $page  = int(Request::input('page', 1));
        $photo = Photo::query()->find($id);

        if (! $photo) {
            abort(404, 'Данной фотографии не существует!');
        }

        if (Request::isMethod('post')) {
            $token  = check(Request::input('token'));
            $title  = check(Request::input('title'));
            $text   = check(Request::input('text'));
            $closed = empty(Request::input('closed')) ? 0 : 1;

            $validator = new Validator();
            $validator->equal($token, $_SESSION['token'], 'Неверный идентификатор сессии, повторите действие!')
                ->length($title, 5, 50, ['title' => 'Слишком длинное или короткое название!'])
                ->length($text, 0, 1000, ['text' => 'Слишком длинное описание (Необходимо не более 1000 символов)!']);

            if ($validator->isValid()) {

                $text = antimat($text);

                $photo->update([
                    'title'  => $title,
                    'text'   => $text,
                    'closed' => $closed
                ]);

                setFlash('success', 'Фотография успешно отредактирована!');
                redirect('/admin/photos?page=' . $page);
            } else {
                setInput(Request::all());
                setFlash('danger', $validator->getErrors());
            }
        }

        return view('admin/photos/edit', compact('photo', 'page'));
    }

    /**
     * Удаление записей
     *
     * @param int $id
     * @return void
     * @throws \Exception
     */
    public function delete($id): void
    {
        if (! is_writable(UPLOADS . '/photos')){
            abort('default', 'Директория c фотографиями недоступна для записи!');
        }

        $page  = int(Request::input('page', 1));
        $token = check(Request::input('token'));

        $photo = Photo::query()->find($id);

        if (! $photo) {
            abort(404, 'Данной фотографии не существует!');
        }

        $validator = new Validator();
        $validator->equal($token, $_SESSION['token'], 'Неверный идентификатор сессии, повторите действие!');

        if ($validator->isValid()) {

            $photo->comments()->delete();
            $photo->delete();

            setFlash('success', 'Фотография успешно удалена!');
        } else {
            setFlash('danger', $validator->getErrors());
        }

        redirect('/admin/photos?page=' . $page);
    }

    /**
     * Пересчет комментариев
     *
     * @return void
     */
    public function restatement(): void
    {
        $token = check(Request::input('token'));

        if (isAdmin(User::BOSS)) {
            if ($token === $_SESSION['token']) {

                restatement('photos');

                setFlash('success', 'Комментарии успешно пересчитаны!');
                redirect('/admin/photos');
            } else {
                abort('default', 'Неверный идентификатор сессии, повторите действие!');
            }
        } else {
            abort('default', 'Пересчитывать комментарии могут только суперадмины!');
        }
    }
}
