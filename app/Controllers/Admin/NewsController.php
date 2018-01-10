<?php

namespace App\Controllers\Admin;

use App\Classes\Request;
use App\Classes\Validator;
use App\Models\Comment;
use App\Models\News;
use App\Models\Setting;
use App\Models\User;
use Illuminate\Database\Capsule\Manager as DB;
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
            ->offset($page['offset'])
            ->limit($page['limit'])
            ->with('user')
            ->get();

        return view('admin/news/index', compact('news', 'page'));
    }

    /**
     * Редактирование новости
     */
    public function edit($id)
    {
        $page   = int(Request::input('page', 1));
        $news   = News::query()->find($id);

        if (! $news) {
            abort(404, 'Новость не существует, возможно она была удалена!');
        }

        if (Request::isMethod('post')) {
            $token  = check(Request::input('token'));
            $title  = check(Request::input('title'));
            $text   = check(Request::input('text'));
            $image  = Request::file('image');
            $closed = Request::has('closed') ? 1 : 0;
            $top    = Request::has('top') ? 1 : 0;

            $validator = new Validator();
            $validator->equal($token, $_SESSION['token'], 'Неверный идентификатор сессии, повторите действие!')
                ->length($title, 5, 50, ['title' => 'Слишком длинный или короткий заголовок новости!'])
                ->length($text, 5, 10000, ['text' => 'Слишком длинный или короткий текст новости!']);

            $rules = [
                'maxsize'   => setting('filesize'),
                'maxweight' => setting('fileupfoto'),
                'minweight' => 100,
            ];

            $validator->image($image, $rules, ['image' => 'Не удалось загрузить фотографию!'], false);

            if ($validator->isValid()) {

                // Удаление старой картинки
                if ($image) {
                    deleteImage('uploads/news/', $news->image);
                    $image = uploadImage($image, UPLOADS.'/news/');
                }

                $news->update([
                    'title'  => $title,
                    'text'   => $text,
                    'closed' => $closed,
                    'top'    => $top,
                    'image'  => $image ?? $news->image,
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
            $closed = Request::has('closed') ? 1 : 0;
            $top    = Request::has('top') ? 1 : 0;

            $validator = new Validator();
            $validator->equal($token, $_SESSION['token'], 'Неверный идентификатор сессии, повторите действие!')
                ->length($title, 5, 50, ['title' => 'Слишком длинный или короткий заголовок новости!'])
                ->length($text, 5, 10000, ['text' => 'Слишком длинный или короткий текст новости!']);

            $rules = [
                'maxsize'   => setting('filesize'),
                'maxweight' => setting('fileupfoto'),
                'minweight' => 100,
            ];

            $validator->image($image, $rules, ['image' => 'Не удалось загрузить фотографию!'], false);

            if ($validator->isValid()) {

                if ($image) {
                    $image = uploadImage($image, UPLOADS.'/news/');
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
                    saveSetting();
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
    public function restatement()
    {
        if (! isAdmin(User::BOSS)) {
            abort(403, 'Доступ запрещен!');
        }

        $token = check(Request::input('token'));

        if ($token == $_SESSION['token']) {

            restatement('news');

            setFlash('success', 'Комментарии успешно пересчитаны!');
        } else {
            setFlash('danger', 'Ошибка! Неверный идентификатор сессии, повторите действие!');
        }

        redirect('/admin/news');
    }

    /**
     * Удаление комментариев
     */
    public function delete()
    {
        if (! is_writable(UPLOADS.'/news')){
            abort('default', 'Директория c файлами новостей недоступна для записи!');
        }

        $page  = int(Request::input('page', 1));
        $token = check(Request::input('token'));
        $del   = intar(Request::input('del'));

        $validator = new Validator();
        $validator->equal($token, $_SESSION['token'], 'Неверный идентификатор сессии, повторите действие!')
            ->true($del, 'Отсутствуют выбранные записи для удаления!');

        if ($validator->isValid()) {

            $newses = News::query()
                ->whereIn('id', $del)
                ->get();

            if ($newses->isNotEmpty()) {
                foreach ($newses as $news) {
                    deleteImage('uploads/news/', $news->image);

                    $news->comments()->delete();
                    $news->delete();
                }
            }

            setFlash('success', 'Выбранные новости успешно удалены!');
        } else {
            setFlash('danger', $validator->getErrors());
        }

        redirect('/admin/news?page=' . $page);
    }
}
