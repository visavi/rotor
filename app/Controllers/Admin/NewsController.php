<?php

declare(strict_types=1);

namespace App\Controllers\Admin;

use App\Classes\Validator;
use App\Models\News;
use App\Models\Setting;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;

class NewsController extends AdminController
{
    /**
     * Конструктор
     */
    public function __construct()
    {
        parent::__construct();

        if (! isAdmin(User::ADMIN)) {
            abort(403, __('errors.forbidden'));
        }
    }

    /**
     * Главная страница
     *
     * @return string
     */
    public function index(): string
    {
        $news = News::query()
            ->orderByDesc('created_at')
            ->with('user')
            ->paginate(setting('postnews'));

        return view('admin/news/index', compact('news'));
    }

    /**
     * Редактирование новости
     *
     * @param int       $id
     * @param Request   $request
     * @param Validator $validator
     * @return string
     */
    public function edit(int $id, Request $request, Validator $validator): string
    {
        /** @var News $news */
        $news = News::query()->find($id);
        $page = int($request->input('page', 1));

        if (! $news) {
            abort(404, __('news.news_not_exist'));
        }

        if ($request->isMethod('post')) {
            $token  = check($request->input('token'));
            $title  = check($request->input('title'));
            $text   = check($request->input('text'));
            $image  = $request->file('image');
            $closed = empty($request->input('closed')) ? 0 : 1;
            $top    = empty($request->input('top')) ? 0 : 1;

            $validator->equal($token, $_SESSION['token'], __('validator.token'))
                ->length($title, 5, 50, ['title' => __('validator.text')])
                ->length($text, 5, 10000, ['text' => __('validator.text')]);

            $rules = [
                'maxsize'   => setting('filesize'),
                'minweight' => 100,
            ];

            $validator->file($image, $rules, ['image' => __('validator.upload_failed')], false);

            if ($validator->isValid()) {
                // Удаление старой картинки
                if ($image) {
                    deleteFile(HOME . $news->image);
                    $file = $news->uploadFile($image, false);
                }

                $news->update([
                    'title'  => $title,
                    'text'   => $text,
                    'closed' => $closed,
                    'top'    => $top,
                    'image'  => $file['path'] ?? $news->image,
                 ]);

                clearCache(['statNews', 'lastNews']);
                setFlash('success', 'Новость успешно отредактирована!');
                redirect('/admin/news/edit/' . $news->id . '?page=' . $page);
            } else {
                setInput($request->all());
                setFlash('danger', $validator->getErrors());
            }
        }

        return view('admin/news/edit', compact('news', 'page'));
    }

    /**
     * Создание новости
     *
     * @param Request   $request
     * @param Validator $validator
     * @return string
     */
    public function create(Request $request, Validator $validator): string
    {
        if ($request->isMethod('post')) {
            $token  = check($request->input('token'));
            $title  = check($request->input('title'));
            $text   = check($request->input('text'));
            $image  = $request->file('image');
            $closed = empty($request->input('closed')) ? 0 : 1;
            $top    = empty($request->input('top')) ? 0 : 1;

            $validator->equal($token, $_SESSION['token'], __('validator.token'))
                ->length($title, 5, 50, ['title' => __('validator.text')])
                ->length($text, 5, 10000, ['text' => __('validator.text')]);

            $rules = [
                'maxsize'   => setting('filesize'),
                'minweight' => 100,
            ];

            $validator->file($image, $rules, ['image' => __('validator.upload_failed')], false);

            if ($validator->isValid()) {
                if ($image) {
                    $file = (new News())->uploadFile($image, false);
                }

                /** @var News $news */
                $news = News::query()->create([
                    'user_id'    => getUser('id'),
                    'title'      => $title,
                    'text'       => $text,
                    'closed'     => $closed,
                    'top'        => $top,
                    'image'      => $file['path'] ?? null,
                    'created_at' => SITETIME,
                ]);

                // Выводим на главную если там нет новостей
                if ($top && empty(setting('lastnews'))) {
                    Setting::query()->where('name', 'lastnews')->update(['value' => 1]);
                    clearCache('settings');
                }

                clearCache(['statNews', 'lastNews']);
                setFlash('success', 'Новость успешно добавлена!');
                redirect('/admin/news/edit/' . $news->id);
            } else {
                setInput($request->all());
                setFlash('danger', $validator->getErrors());
            }
        }

        return view('admin/news/create');
    }

    /**
     * Пересчет комментариев
     *
     * @param Request $request
     * @return void
     */
    public function restatement(Request $request): void
    {
        if (! isAdmin(User::BOSS)) {
            abort(403, __('errors.forbidden'));
        }

        $token = check($request->input('token'));

        if ($token === $_SESSION['token']) {

            restatement('news');

            setFlash('success', 'Комментарии успешно пересчитаны!');
        } else {
            setFlash('danger', __('validator.token'));
        }

        redirect('/admin/news');
    }

    /**
     * Удаление новостей
     *
     * @param int       $id
     * @param Request   $request
     * @param Validator $validator
     * @return void
     * @throws \Exception
     */
    public function delete(int $id, Request $request, Validator $validator): void
    {
        $page  = int($request->input('page', 1));
        $token = check($request->input('token'));

        /** @var News $news */
        $news = News::query()->find($id);

        if (! $news) {
            abort(404, __('news.news_not_exist'));
        }

        $validator->equal($token, $_SESSION['token'], __('validator.token'));

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
