<?php

declare(strict_types=1);

namespace App\Http\Controllers\Admin;

use App\Classes\Validator;
use App\Models\File;
use App\Models\News;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class NewsController extends AdminController
{
    /**
     * Главная страница
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
     */
    public function edit(int $id, Request $request, Validator $validator): View|RedirectResponse
    {
        $news = News::query()->find($id);
        $page = int($request->input('page', 1));

        if (! $news) {
            abort(404, __('news.news_not_exist'));
        }

        if ($request->isMethod('post')) {
            $title = $request->input('title');
            $text = $request->input('text');
            $closed = empty($request->input('closed')) ? 0 : 1;
            $top = empty($request->input('top')) ? 0 : 1;

            $validator
                ->length($title, setting('news_title_min'), setting('news_title_max'), ['title' => __('validator.text')])
                ->length($text, setting('news_text_min'), setting('news_text_max'), ['text' => __('validator.text')]);

            if ($validator->isValid()) {
                $news->update([
                    'title'  => $title,
                    'text'   => $text,
                    'closed' => $closed,
                    'top'    => $top,
                ]);

                clearCache(['statNews', 'pinnedNews', 'NewsFeed']);
                setFlash('success', __('news.news_success_edited'));

                return redirect()->route('news.view', ['id' => $news->id]);
            }

            setInput($request->all());
            setFlash('danger', $validator->getErrors());
        }

        return view('admin/news/edit', compact('news', 'page'));
    }

    /**
     * Создание новости
     */
    public function create(Request $request, Validator $validator): View|RedirectResponse
    {
        $files = File::query()
            ->where('relate_type', News::$morphName)
            ->where('relate_id', 0)
            ->where('user_id', getUser('id'))
            ->orderBy('created_at');

        if ($request->isMethod('post')) {
            $title = $request->input('title');
            $text = $request->input('text');
            $closed = empty($request->input('closed')) ? 0 : 1;
            $top = empty($request->input('top')) ? 0 : 1;

            $validator
                ->length($title, setting('news_title_min'), setting('news_title_max'), ['title' => __('validator.text')])
                ->length($text, setting('news_text_min'), setting('news_text_max'), ['text' => __('validator.text')]);

            if ($validator->isValid()) {
                $news = News::query()->create([
                    'user_id'    => getUser('id'),
                    'title'      => $title,
                    'text'       => $text,
                    'closed'     => $closed,
                    'top'        => $top,
                    'created_at' => SITETIME,
                ]);

                $files->update(['relate_id' => $news->id]);

                clearCache(['statNews', 'pinnedNews', 'statNewsDate', 'NewsFeed']);
                setFlash('success', __('news.news_success_added'));

                return redirect()->route('news.view', ['id' => $news->id]);
            }

            setInput($request->all());
            setFlash('danger', $validator->getErrors());
        }

        $files = $files->orderBy('created_at')->get();

        return view('admin/news/create', compact('files'));
    }

    /**
     * Пересчет комментариев
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

        return redirect()->route('admin.news.index');
    }

    /**
     * Удаление новостей
     */
    public function delete(int $id, Request $request, Validator $validator): RedirectResponse
    {
        $page = int($request->input('page', 1));

        $news = News::query()->find($id);

        if (! $news) {
            abort(404, __('news.news_not_exist'));
        }

        $validator->equal($request->input('_token'), csrf_token(), __('validator.token'));

        if ($validator->isValid()) {
            $news->delete();

            setFlash('success', __('news.news_success_deleted'));
        } else {
            setFlash('danger', $validator->getErrors());
        }

        return redirect()->route('admin.news.index', ['page' => $page]);
    }
}
