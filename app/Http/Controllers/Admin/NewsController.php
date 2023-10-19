<?php

declare(strict_types=1);

namespace App\Http\Controllers\Admin;

use App\Classes\Validator;
use App\Models\News;
use App\Models\Setting;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class NewsController extends AdminController
{
    /**
     * Главная страница
     *
     * @return View
     */
    public function index(): View
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
     *
     * @return View|RedirectResponse
     */
    public function edit(int $id, Request $request, Validator $validator)
    {
        /** @var News $news */
        $news = News::query()->find($id);
        $page = int($request->input('page', 1));

        if (! $news) {
            abort(404, __('news.news_not_exist'));
        }

        if ($request->isMethod('post')) {
            $title  = $request->input('title');
            $text   = $request->input('text');
            $image  = $request->file('image');
            $closed = empty($request->input('closed')) ? 0 : 1;
            $top    = empty($request->input('top')) ? 0 : 1;

            $validator->equal($request->input('_token'), csrf_token(), __('validator.token'))
                ->length($title, 3, 50, ['title' => __('validator.text')])
                ->length($text, 5, 10000, ['text' => __('validator.text')]);

            $rules = [
                'maxsize'    => setting('filesize'),
                'extensions' => explode(',', setting('image_extensions')),
                'minweight'  => 100,
            ];

            $validator->file($image, $rules, ['image' => __('validator.image_upload_failed')], false);

            if ($validator->isValid()) {
                // Удаление старой картинки
                if ($image) {
                    deleteFile(public_path($news->image));
                    $file = $news->uploadFile($image, false);
                }

                $news->update([
                    'title'  => $title,
                    'text'   => $text,
                    'closed' => $closed,
                    'top'    => $top,
                    'image'  => $file['path'] ?? $news->image,
                 ]);

                clearCache(['statNews', 'lastNews', 'NewsFeed']);
                setFlash('success', __('news.news_success_edited'));

                return redirect('admin/news/edit/' . $news->id . '?page=' . $page);
            }

            setInput($request->all());
            setFlash('danger', $validator->getErrors());
        }

        return view('admin/news/edit', compact('news', 'page'));
    }

    /**
     * Создание новости
     *
     * @param Request   $request
     * @param Validator $validator
     *
     * @return View|RedirectResponse
     */
    public function create(Request $request, Validator $validator)
    {
        if ($request->isMethod('post')) {
            $title  = $request->input('title');
            $text   = $request->input('text');
            $image  = $request->file('image');
            $closed = empty($request->input('closed')) ? 0 : 1;
            $top    = empty($request->input('top')) ? 0 : 1;

            $validator->equal($request->input('_token'), csrf_token(), __('validator.token'))
                ->length($title, 3, 50, ['title' => __('validator.text')])
                ->length($text, 5, 10000, ['text' => __('validator.text')]);

            $rules = [
                'maxsize'    => setting('filesize'),
                'extensions' => explode(',', setting('image_extensions')),
                'minweight'  => 100,
            ];

            $validator->file($image, $rules, ['image' => __('validator.image_upload_failed')], false);

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

                clearCache(['statNews', 'lastNews', 'statNewsDate', 'NewsFeed']);
                setFlash('success', __('news.news_success_added'));

                return redirect('admin/news/edit/' . $news->id);
            }

            setInput($request->all());
            setFlash('danger', $validator->getErrors());
        }

        return view('admin/news/create');
    }

    /**
     * Пересчет комментариев
     *
     * @param Request $request
     *
     * @return RedirectResponse
     */
    public function restatement(Request $request): RedirectResponse
    {
        if (! isAdmin(User::BOSS)) {
            abort(403, __('errors.forbidden'));
        }

        if ($request->input('_token') === csrf_token()) {
            restatement('news');

            setFlash('success', __('main.success_recounted'));
        } else {
            setFlash('danger', __('validator.token'));
        }

        return redirect('admin/news');
    }

    /**
     * Удаление новостей
     *
     * @param int       $id
     * @param Request   $request
     * @param Validator $validator
     *
     * @return RedirectResponse
     */
    public function delete(int $id, Request $request, Validator $validator): RedirectResponse
    {
        $page = int($request->input('page', 1));

        /** @var News $news */
        $news = News::query()->find($id);

        if (! $news) {
            abort(404, __('news.news_not_exist'));
        }

        $validator->equal($request->input('_token'), csrf_token(), __('validator.token'));

        if ($validator->isValid()) {
            deleteFile(public_path($news->image));

            $news->comments()->delete();
            $news->delete();

            setFlash('success', __('news.news_success_deleted'));
        } else {
            setFlash('danger', $validator->getErrors());
        }

        return redirect('admin/news?page=' . $page);
    }
}
