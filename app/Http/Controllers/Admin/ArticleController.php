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
use Illuminate\Support\Facades\Date;
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

        $new = Article::query()
            ->active(false)
            ->where('draft', false)
            ->count();

        return view('admin/blogs/index', compact('categories', 'new'));
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

        $validator->length($name, setting('blog_category_min'), setting('blog_category_max'), ['name' => __('validator.text')]);

        if ($validator->isValid()) {
            $max = Blog::query()->max('sort') + 1;

            $category = Blog::query()->create([
                'name' => $name,
                'sort' => $max,
            ]);

            setFlash('success', __('blogs.category_success_created'));

            return redirect()->route('admin.blogs.edit', ['id' => $category->id]);
        }

        setInput($request->all());
        setFlash('danger', $validator->getErrors());

        return redirect()->route('admin.blogs.index');
    }

    /**
     * Редактирование раздела
     */
    public function edit(int $id, Request $request, Validator $validator): View|RedirectResponse
    {
        if (! isAdmin(User::BOSS)) {
            abort(403, __('errors.forbidden'));
        }

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

            $validator
                ->length($name, setting('blog_category_min'), setting('blog_category_max'), ['title' => __('validator.text')])
                ->notEqual($parent, $category->id, ['parent' => __('blogs.category_not_exist')]);

            if ($validator->isValid()) {
                $category->update([
                    'parent_id' => $parent,
                    'name'      => $name,
                    'sort'      => $sort,
                    'closed'    => $closed,
                ]);

                setFlash('success', __('blogs.category_success_edited'));

                return redirect()->route('admin.blogs.index');
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

        $category = Blog::query()->with('children')->find($id);

        if (! $category) {
            abort(404, __('blogs.category_not_exist'));
        }

        $validator->equal($request->input('_token'), csrf_token(), __('validator.token'))
            ->true($category->children->isEmpty(), __('blogs.category_has_subcategories'));

        $article = Article::query()->where('category_id', $category->id)->exists();
        if ($article) {
            $validator->addError(__('blogs.articles_in_category'));
        }

        if ($validator->isValid()) {
            $category->delete();

            setFlash('success', __('blogs.category_success_deleted'));
        } else {
            setFlash('danger', $validator->getErrors());
        }

        return redirect()->route('admin.blogs.index');
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

        return redirect()->route('admin.blogs.index');
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
            ->active()
            ->where('category_id', $id)
            ->orderByDesc('created_at')
            ->with('user')
            ->paginate(setting('blogpost'));

        return view('admin/blogs/blog', compact('articles', 'category'));
    }

    /**
     * Редактирование статьи
     */
    public function editArticle(int $id, Request $request, Validator $validator): View|RedirectResponse
    {
        $article = Article::query()->find($id);

        if (! $article) {
            abort(404, __('blogs.article_not_exist'));
        }

        if ($request->isMethod('post')) {
            $cid = int($request->input('cid'));
            $title = $request->input('title');
            $text = $request->input('text');
            $tags = (array) $request->input('tags');
            $tags = array_unique(array_diff($tags, ['']));
            $published = $request->input('published');

            $isDelay = (bool) $request->input('delay');
            $isPublish = $request->input('action') === 'publish';

            $category = Blog::query()->find($cid);

            $validator
                ->length($title, setting('blog_title_min'), setting('blog_title_max'), ['title' => __('validator.text')])
                ->length($text, setting('blog_text_min'), setting('blog_text_max'), ['text' => __('validator.text')])
                ->notEmpty($category, ['cid' => __('blogs.category_not_exist')])
                ->between(count($tags), 1, 10, ['tags' => __('blogs.article_count_tags')]);

            if ($isDelay && Date::parse($published) < now()) {
                $validator->addError(['published' => __('blogs.article_delayed_time')]);
            }

            foreach ($tags as $tag) {
                $validator->length($tag, setting('blog_tag_min'), setting('blog_tag_max'), ['tags' => __('blogs.article_error_tags')]);
            }

            if ($category) {
                $validator->empty($category->closed, ['cid' => __('blogs.category_closed')]);
            }

            if ($validator->isValid()) {
                $isDraft = $article->draft && ! $isPublish;
                $isActive = ! $isDraft && ! $isDelay;

                $article->update([
                    'category_id'  => $category->id,
                    'title'        => $title,
                    'text'         => $text,
                    'draft'        => $isDraft,
                    'active'       => $isActive,
                    'published_at' => $isDelay ? $published : null,
                ]);

                $tagIds = [];
                foreach ($tags as $key => $tagName) {
                    $tag = Tag::query()->firstOrCreate(['name' => Str::lower($tagName)]);
                    $tagIds[$tag->id] = ['sort' => $key];
                }

                $article->tags()->sync($tagIds);
                clearCache('tagCloud');

                $flash = $isDraft ? __('blogs.article_success_edited') : __('blogs.article_success_created');

                return redirect()
                    ->route('articles.view', ['slug' => $article->slug])
                    ->with('success', $flash);
            }

            setInput($request->all());
            setFlash('danger', $validator->getErrors());
        }

        $categories = $article->category->getChildren();

        return view('admin/blogs/edit_article', compact('article', 'categories'));
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

            clearCache('tagCloud');

            setFlash('success', __('blogs.article_success_deleted'));
        } else {
            setFlash('danger', $validator->getErrors());
        }

        return redirect()->route('admin.blogs.blog', ['id' => $article->category_id, 'page' => $page]);
    }

    /**
     * Публикация статьи
     */
    public function publish(int $id, Request $request): RedirectResponse
    {
        $article = Article::query()->find($id);

        if (! $article) {
            abort(404, __('blogs.article_not_exist'));
        }

        if ($request->input('_token') === csrf_token()) {
            $active = $article->active ^ 1;

            $article->update([
                'active'     => $active,
                'draft'      => false,
                'created_at' => SITETIME,
            ]);

            if ($active) {
                $status = __('blogs.article_success_published');
                $text = textNotice('article_publish', ['url' => route('articles.view', ['slug' => $article->slug], false), 'title' => $article->title]);
            } else {
                $status = __('blogs.article_success_unpublished');
                $text = textNotice('article_unpublish', ['url' => route('articles.view', ['slug' => $article->slug], false), 'title' => $article->title]);
            }

            $article->user->sendMessage(null, $text);
            $flash = ['success', $status];
        } else {
            $flash = ['danger', __('validator.token')];
        }

        return redirect()
            ->route('admin.articles.edit', ['id' => $article->id])
            ->with(...$flash);
    }

    /**
     * Новые статьи
     */
    public function new(): View
    {
        $articles = Article::query()
            ->active(false)
            ->where('draft', false)
            ->orderByDesc('created_at')
            ->with('user', 'category')
            ->paginate(setting('blogpost'));

        return view('admin/blogs/new', compact('articles'));
    }
}
