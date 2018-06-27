<?php

namespace App\Controllers\Admin;

use App\Classes\Request;
use App\Classes\Validator;
use App\Models\News;
use App\Models\Setting;
use App\Models\User;

class NewsController extends AdminController
{
    /**
     * Конструктор
     */
    public function __construct()
    {
        parent::__construct();

        if (! isAdmin(User::ADMIN)) {
            abort(403, 'Доступ запрещен!');
        }
    }

    /**
     * Главная страница
     */
    public function index()
    {
        $total = News::query()->count();
        $page = paginate(setting('postnews'), $total);

        $news = News::query()
            ->orderBy('created_at', 'desc')
            ->offset($page->offset)
            ->limit($page->limit)
            ->with('user')
            ->get();

        return view('admin/news/index', compact('news', 'page'));
    }

    /**
     * Редактирование новости
     */
    public function edit($id)
    {
        $page = int(Request::input('page', 1));
        $news = News::query()->find($id);

        if (! $news) {
            abort(404, 'Новость не существует, возможно она была удалена!');
        }

        if (Request::isMethod('post')) {
            $token  = check(Request::input('token'));
            $title  = check(Request::input('title'));
            $text   = check(Request::input('text'));
            $image  = Request::file('image');
            $closed = empty(Request::input('closed')) ? 0 : 1;
            $top    = empty(Request::input('top')) ? 0 : 1;

            $validator = new Validator();
            $validator->equal($token, $_SESSION['token'], 'Неверный идентификатор сессии, повторите действие!')
                ->length($title, 5, 50, ['title' => 'Слишком длинный или короткий заголовок новости!'])
                ->length($text, 5, 10000, ['text' => 'Слишком длинный или короткий текст новости!']);

            $rules = [
                'maxsize'   => setting('filesize'),
                'minweight' => 100,
            ];

            $validator->file($image, $rules, ['image' => 'Не удалось загрузить фотографию!'], false);

            if ($validator->isValid()) {

                // Удаление старой картинки
                if ($image) {
                    deleteFile(HOME . $news->image);
                    $image = $news->uploadFile($image);
                }

                $news->update([
                    'title'  => $title,
                    'text'   => $text,
                    'closed' => $closed,
                    'top'    => $top,
                    'image'  => $image ? $image['filename'] : $news->image,
                 ]);

                setFlash('success', 'Новость успешно отредактирована!');
                redirect('/admin/news/edit/' . $news->id . '?page=' . $page);
            } else {
                setInput(Request::all());
                setFlash('danger', $validator->getErrors());
            }
        }

        return view('admin/news/edit', compact('news', 'page'));
    }

    /**
     * Создание новости
     */
    public function create()
    {
        if (Request::isMethod('post')) {
            $token  = check(Request::input('token'));
            $title  = check(Request::input('title'));
            $text   = check(Request::input('text'));
            $image  = Request::file('image');
            $closed = empty(Request::input('closed')) ? 0 : 1;
            $top    = empty(Request::input('top')) ? 0 : 1;

            $validator = new Validator();
            $validator->equal($token, $_SESSION['token'], 'Неверный идентификатор сессии, повторите действие!')
                ->length($title, 5, 50, ['title' => 'Слишком длинный или короткий заголовок новости!'])
                ->length($text, 5, 10000, ['text' => 'Слишком длинный или короткий текст новости!']);

            $rules = [
                'maxsize'   => setting('filesize'),
                'minweight' => 100,
            ];

            $validator->file($image, $rules, ['image' => 'Не удалось загрузить фотографию!'], false);

            if ($validator->isValid()) {

                if ($image) {
                    $image = basename((new News())->uploadFile($image));
                }

                $news = News::query()->create([
                    'user_id'    => getUser('id'),
                    'title'      => $title,
                    'text'       => $text,
                    'closed'     => $closed,
                    'top'        => $top,
                    'image'      => $image ?? null,
                    'created_at' => SITETIME,
                ]);



                // Выводим на главную если там нет новостей
                if ($top && empty(setting('lastnews'))) {
                    Setting::query()->where('name', 'lastnews')->update(['value' => 1]);
                    saveSettings();
                }

                setFlash('success', 'Новость успешно добавлена!');
                redirect('/admin/news/edit/' . $news->id);
            } else {
                setInput(Request::all());
                setFlash('danger', $validator->getErrors());
            }
        }

        return view('admin/news/create');
    }

    /**
     * Пересчет комментариев
     */
    public function restatement(): void
    {
        if (! isAdmin(User::BOSS)) {
            abort(403, 'Доступ запрещен!');
        }

        $token = check(Request::input('token'));

        if ($token === $_SESSION['token']) {

            restatement('news');

            setFlash('success', 'Комментарии успешно пересчитаны!');
        } else {
            setFlash('danger', 'Ошибка! Неверный идентификатор сессии, повторите действие!');
        }

        redirect('/admin/news');
    }

    /**
     * Удаление новостей
     *
     * @param int $id
     */
    public function delete($id): void
    {
        $page  = int(Request::input('page', 1));
        $token = check(Request::input('token'));

        $news = News::query()->find($id);

        if (! $news) {
            abort(404, 'Новость не существует, возможно она была удалена!');
        }

        $validator = new Validator();
        $validator->equal($token, $_SESSION['token'], 'Неверный идентификатор сессии, повторите действие!');

        if ($validator->isValid()) {

            deleteFile(HOME . $news->image);

            $news->comments()->delete();
            $news->delete();

            setFlash('success', 'Новость успешно удалена!');
        } else {
            setFlash('danger', $validator->getErrors());
        }

        redirect('/admin/news?page=' . $page);
    }
}
