<?php

declare(strict_types=1);

namespace App\Http\Controllers\Admin;

use App\Classes\Validator;
use App\Models\Article;
use App\Models\Blog;
use App\Models\Tag;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\View\View;

class ArticleController extends AdminController
{
    /**
     * Главная страница
     */
    public function index(): View
    {
        $categories = Blog::query()
            ->where('parent_id', 0)
            ->orderBy('sort')
            ->with('children', 'new', 'children.new', 'lastArticle.user')
            ->get();

        return view('admin/blogs/index', compact('categories'));
    }

    /**
     * Создание раздела
     */
    public function create(Request $request, Validator $validator): RedirectResponse
    {
        if (! isAdmin(User::BOSS)) {
            abort(403, __('errors.forbidden'));
        }

        $name = $request->input('name');

        $validator->equal($request->input('_token'), csrf_token(), __('validator.token'))
            ->length($name, 3, 50, ['name' => __('validator.text')]);

        if ($validator->isValid()) {
            $max = Blog::query()->max('sort') + 1;

            /** @var Blog $category */
            $category = Blog::query()->create([
                'name' => $name,
                'sort' => $max,
            ]);

            setFlash('success', __('blogs.category_success_created'));

            return redirect('admin/blogs/edit/' . $category->id);
        }

        setInput($request->all());
        setFlash('danger', $validator->getErrors());

        return redirect('admin/blogs');
    }

    /**
     * Редактирование раздела
     *
     *
     * @return View|RedirectResponse
     */
    public function edit(int $id, Request $request, Validator $validator)
    {
        if (! isAdmin(User::BOSS)) {
            abort(403, __('errors.forbidden'));
        }

        /** @var Blog $category */
        $category = Blog::query()->with('children')->find($id);

        if (! $category) {
            abort(404, __('blogs.category_not_exist'));
        }

        $categories = $category->getChildren();

        if ($request->isMethod('post')) {
            $parent = int($request->input('parent'));
            $name = $request->input('name');
            $sort = int($request->input('sort'));
            $closed = empty($request->input('closed')) ? 0 : 1;

            $validator->equal($request->input('_token'), csrf_token(), __('validator.token'))
                ->length($name, 3, 50, ['title' => __('validator.text')])
                ->notEqual($parent, $category->id, ['parent' => __('blogs.category_not_exist')]);

            if ($validator->isValid()) {
                $category->update([
                    'parent_id' => $parent,
                    'name'      => $name,
                    'sort'      => $sort,
                    'closed'    => $closed,
                ]);

                setFlash('success', __('blogs.category_success_edited'));

                return redirect('admin/blogs');
            }

            setInput($request->all());
            setFlash('danger', $validator->getErrors());
        }

        return view('admin/blogs/edit', compact('categories', 'category'));
    }

    /**
     * Удаление раздела
     */
    public function delete(int $id, Request $request, Validator $validator): RedirectResponse
    {
        if (! isAdmin(User::BOSS)) {
            abort(403, __('errors.forbidden'));
        }

        /** @var Blog $category */
        $category = Blog::query()->with('children')->find($id);

        if (! $category) {
            abort(404, __('blogs.category_not_exist'));
        }

        $validator->equal($request->input('_token'), csrf_token(), __('validator.token'))
            ->true($category->children->isEmpty(), __('blogs.category_has_subcategories'));

        $article = Article::query()->where('category_id', $category->id)->first();
        if ($article) {
            $validator->addError(__('blogs.articles_in_category'));
        }

        if ($validator->isValid()) {
            $category->delete();

            setFlash('success', __('blogs.category_success_deleted'));
        } else {
            setFlash('danger', $validator->getErrors());
        }

        return redirect('admin/blogs');
    }

    /**
     * Пересчет данных
     */
    public function restatement(Request $request): RedirectResponse
    {
        if (! isAdmin(User::BOSS)) {
            abort(403, __('errors.forbidden'));
        }

        if ($request->input('_token') === csrf_token()) {
            restatement('blogs');

            setFlash('success', __('main.success_recounted'));
        } else {
            setFlash('danger', __('validator.token'));
        }

        return redirect('admin/blogs');
    }

    /**
     * Список блогов
     */
    public function blog(int $id): View
    {
        $category = Blog::query()->with('parent')->find($id);

        if (! $category) {
            abort(404, __('blogs.category_not_exist'));
        }

        $articles = Article::query()
            ->where('category_id', $id)
            ->orderByDesc('created_at')
            ->with('user')
            ->paginate(setting('blogpost'));

        return view('admin/blogs/blog', compact('articles', 'category'));
    }

    /**
     * Редактирование статьи
     *
     *
     * @return View|RedirectResponse
     */
    public function editArticle(int $id, Request $request, Validator $validator)
    {
        $article = Article::query()->find($id);

        if (! $article) {
            abort(404, __('blogs.article_not_exist'));
        }

        if ($request->isMethod('post')) {
            $title = $request->input('title');
            $text = $request->input('text');
            $tags = (array) $request->input('tags');
            $tags = array_unique(array_diff($tags, ['']));

            $validator
                ->equal($request->input('_token'), csrf_token(), __('validator.token'))
                ->length($title, 3, 50, ['title' => __('validator.text')])
                ->length($text, 100, setting('maxblogpost'), ['text' => __('validator.text')])
                ->between(count($tags), 1, 10, ['tags' => __('blogs.article_count_tags')]);

            foreach ($tags as $tag) {
                $validator->length($tag, 2, 30, ['tags' => __('blogs.article_error_tags')]);
            }

            if ($validator->isValid()) {
                $article->update([
                    'title' => $title,
                    'text'  => $text,
                ]);

                $tagIds = [];
                foreach ($tags as $key => $tagName) {
                    $tag = Tag::query()->firstOrCreate(['name' => Str::lower($tagName)]);
                    $tagIds[$tag->id] = ['sort' => $key];
                }

                $article->tags()->sync($tagIds);

                clearCache(['statArticles', 'recentArticles', 'ArticleFeed']);
                setFlash('success', __('blogs.article_success_edited'));

                return redirect('articles/' . $article->id);
            }

            setInput($request->all());
            setFlash('danger', $validator->getErrors());
        }

        return view('admin/blogs/edit_blog', compact('article'));
    }

    /**
     * Перенос статьи
     *
     *
     * @return View|RedirectResponse
     */
    public function moveArticle(int $id, Request $request, Validator $validator)
    {
        $article = Article::query()->find($id);

        if (! $article) {
            abort(404, __('blogs.article_not_exist'));
        }

        if ($request->isMethod('post')) {
            $cid = int($request->input('cid'));

            /** @var Blog $category */
            $category = Blog::query()->find($cid);

            $validator
                ->equal($request->input('_token'), csrf_token(), __('validator.token'))
                ->notEmpty($category, ['cid' => __('blogs.category_not_exist')]);

            if ($category) {
                $validator->empty($category->closed, ['cid' => __('blogs.category_closed')]);
                $validator->notEqual($article->category_id, $category->id, ['cid' => __('blogs.article_error_moving')]);
            }

            if ($validator->isValid()) {
                // Обновление счетчиков
                $category->increment('count_articles');
                Blog::query()->where('id', $article->category_id)->decrement('count_articles');

                $article->update([
                    'category_id' => $category->id,
                ]);

                setFlash('success', __('blogs.article_success_moved'));

                return redirect('articles/' . $article->id);
            }

            setInput($request->all());
            setFlash('danger', $validator->getErrors());
        }

        $categories = (new Blog())->getChildren();

        return view('admin/blogs/move_blog', compact('article', 'categories'));
    }

    /**
     * Удаление статьи
     */
    public function deleteArticle(int $id, Request $request, Validator $validator): RedirectResponse
    {
        $page = int($request->input('page', 1));

        $article = Article::query()->find($id);

        if (! $article) {
            abort(404, __('blogs.article_not_exist'));
        }

        $validator->equal($request->input('_token'), csrf_token(), __('validator.token'));

        if ($validator->isValid()) {
            $article->delete();

            $article->category->decrement('count_articles');

            clearCache(['statArticles', 'recentArticles', 'ArticleFeed']);
            setFlash('success', __('blogs.article_success_deleted'));
        } else {
            setFlash('danger', $validator->getErrors());
        }

        return redirect('admin/blogs/' . $article->category_id . '?page=' . $page);
    }
}
