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
     */
    public function index()
    {
        $total = Photo::count();
        $page = paginate(setting('fotolist'), $total);
        $photos = Photo::orderBy('created_at', 'desc')
            ->offset($page['offset'])
            ->limit(setting('fotolist'))
            ->with('user')
            ->get();

        return view('admin/photo/index', compact('photos', 'page', 'total'));
    }

    /**
     * Редактирование ссылки
     */
    public function edit($id)
    {
        $page = int(Request::input('page', 1));
        $photo = Photo::query()->find($id);

        if (!$photo) {
            abort('default', 'Ошибка! Данной фотографии не существует!');
        }

        if (Request::isMethod('post')) {
            $token = check(Request::input('token'));
            $title = check(Request::input('title'));
            $text = check(Request::input('text'));
            $closed = Request::has('closed') ? 1 : 0;
            $validator = new Validator();
            $validator->equal($token, $_SESSION['token'], 'Неверный идентификатор сессии, повторите действие!')
                ->length($title, 5, 50, ['title' => 'Слишком длинное или короткое название!'])
                ->length($text, 0, 1000, ['text' => 'Слишком длинное описание (Необходимо до 1000 символов)!']);

            if ($validator->isValid()) {
                $photo->title = $title;
                $photo->text = $text;;
                $photo->closed = $closed;
                $photo->save();

                setFlash('success', 'Фотография успешно отредактирована!');
                redirect('/admin/gallery?page=' . $page);
            } else {
                setInput(Request::all());
                setFlash('danger', $validator->getErrors());
            }
        }

        return view('admin/photo/edit', compact('photo', 'page'));
    }

    /**
     * Удаление записей
     */
    public function delete()
    {
        $page = int(Request::input('page', 1));
        $token = check(Request::input('token'));
        $del = intar(Request::input('del'));
        $validator = new Validator();
        $validator->equal($token, $_SESSION['token'], 'Неверный идентификатор сессии, повторите действие!')
            ->true($del, 'Отсутствуют выбранные фотографии!');

        if ($validator->isValid()) {
            Photo::query()->whereIn('id', $del)->delete();

            setFlash('success', 'Выбранные фотографии успешно удалены!');
        } else {
            setFlash('danger', $validator->getErrors());
        }

        redirect('/admin/gallery?page=' . $page);
    }

    /**
     * Пересчет комментариев
     */
    public function restatement()
    {
        $token = check(Request::input('token'));

        if (isAdmin(User::BOSS)) {
            if ($token == $_SESSION['token']) {
                restatement('photo');
                setFlash('success', 'Комментарии успешно пересчитаны!');
                redirect("/admin/gallery");
            } else {
                abort('default', 'Ошибка! Неверный идентификатор сессии, повторите действие!');
            }
        } else {
            abort('default', 'Ошибка! Пересчитывать комментарии могут только суперадмины!');
        }
    }
}
