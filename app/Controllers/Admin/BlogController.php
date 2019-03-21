<?php

declare(strict_types=1);

namespace App\Controllers\Admin;

use App\Classes\Validator;
use App\Models\Blog;
use App\Models\Category;
use App\Models\User;
use Illuminate\Http\Request;

class BlogController extends AdminController
{
    /**
     * Главная страница
     *
     * @return string
     */
    public function index(): string
    {
        $categories = Category::query()
            ->where('parent_id', 0)
            ->orderBy('sort')
            ->with('children', 'new', 'children.new')
            ->get();

        return view('admin/blogs/index', compact('categories'));
    }

    /**
     * Создание раздела
     *
     * @param Request   $request
     * @param Validator $validator
     * @return void
     */
    public function create(Request $request, Validator $validator): void
    {
        if (! isAdmin(User::BOSS)) {
            abort(403, 'Доступ запрещен!');
        }

        $token = check($request->input('token'));
        $name  = check($request->input('name'));

        $validator->equal($token, $_SESSION['token'], trans('validator.token'))
            ->length($name, 3, 50, ['name' => trans('validator.title')]);

        if ($validator->isValid()) {

            $max = Category::query()->max('sort') + 1;

            /** @var Category $category */
            $category = Category::query()->create([
                'name'  => $name,
                'sort'  => $max,
            ]);

            setFlash('success', 'Новый раздел успешно создан!');
            redirect('/admin/blogs/edit/' . $category->id);
        } else {
            setInput($request->all());
            setFlash('danger', $validator->getErrors());
        }

        redirect('/admin/blogs');
    }

    /**
     * Редактирование раздела
     *
     * @param int       $id
     * @param Request   $request
     * @param Validator $validator
     * @return string
     */
    public function edit(int $id, Request $request, Validator $validator): string
    {
        if (! isAdmin(User::BOSS)) {
            abort(403, 'Доступ запрещен!');
        }

        /** @var Category $category */
        $category = Category::query()->with('children')->find($id);

        if (! $category) {
            abort(404, 'Данного раздела не существует!');
        }

        $categories = Category::query()
            ->where('parent_id', 0)
            ->orderBy('sort')
            ->get();

        if ($request->isMethod('post')) {
            $token  = check($request->input('token'));
            $parent = int($request->input('parent'));
            $name   = check($request->input('name'));
            $sort   = check($request->input('sort'));
            $closed = empty($request->input('closed')) ? 0 : 1;

            $validator->equal($token, $_SESSION['token'], trans('validator.token'))
                ->length($name, 3, 50, ['title' => trans('validator.title')])
                ->notEqual($parent, $category->id, ['parent' => 'Недопустимый выбор родительского раздела!']);

            if (! empty($parent) && $category->children->isNotEmpty()) {
                $validator->addError(['parent' => 'Текущий раздел имеет подразделы!']);
            }

            if ($validator->isValid()) {

                $category->update([
                    'parent_id' => $parent,
                    'name'      => $name,
                    'sort'      => $sort,
                    'closed'    => $closed,
                ]);

                setFlash('success', 'Раздел успешно отредактирован!');
                redirect('/admin/blogs');
            } else {
                setInput($request->all());
                setFlash('danger', $validator->getErrors());
            }
        }

        return view('admin/blogs/edit', compact('categories', 'category'));
    }

    /**
     * Удаление раздела
     *
     * @param int       $id
     * @param Request   $request
     * @param Validator $validator
     * @return void
     * @throws \Exception
     */
    public function delete(int $id, Request $request, Validator $validator): void
    {
        if (! isAdmin(User::BOSS)) {
            abort(403, 'Доступ запрещен!');
        }

        /** @var Category $category */
        $category = Category::query()->with('children')->find($id);

        if (! $category) {
            abort(404, 'Данного раздела не существует!');
        }

        $token = check($request->input('token'));

        $validator->equal($token, $_SESSION['token'], trans('validator.token'))
            ->true($category->children->isEmpty(), 'Удаление невозможно! Данный раздел имеет подразделы!');

        $article = Blog::query()->where('category_id', $category->id)->first();
        if ($article) {
            $validator->addError('Удаление невозможно! В данном разделе имеются статьи!');
        }

        if ($validator->isValid()) {

            $category->delete();

            setFlash('success', 'Раздел успешно удален!');
        } else {
            setFlash('danger', $validator->getErrors());
        }

        redirect('/admin/blogs');
    }

    /**
     * Пересчет данных
     *
     * @param Request $request
     * @return void
     */
    public function restatement(Request $request): void
    {
        if (! isAdmin(User::BOSS)) {
            abort(403, 'Доступ запрещен!');
        }

        $token = check($request->input('token'));

        if ($token === $_SESSION['token']) {

            restatement('blogs');

            setFlash('success', 'Данные успешно пересчитаны!');
        } else {
            setFlash('danger', trans('validator.token'));
        }

        redirect('/admin/blogs');
    }

    /**
     * Список блогов
     *
     * @param int $id
     * @return string
     */
    public function blog(int $id): string
    {
        $category = Category::query()->with('parent')->find($id);

        if (! $category) {
            abort(404, 'Данного раздела не существует!');
        }

        $total = Blog::query()->where('category_id', $id)->count();

        $page = paginate(setting('blogpost'), $total);

        $blogs = Blog::query()
            ->where('category_id', $id)
            ->orderBy('created_at', 'desc')
            ->offset($page->offset)
            ->limit($page->limit)
            ->with('user')
            ->get();

        return view('admin/blogs/blog', compact('blogs', 'category', 'page'));
    }

    /**
     * Редактирование статьи
     *
     * @param int       $id
     * @param Request   $request
     * @param Validator $validator
     * @return string
     */
    public function editBlog(int $id, Request $request, Validator $validator): string
    {
        /** @var Blog $blog */
        $blog = Blog::query()->find($id);

        if (! $blog) {
            abort(404, 'Данной статьи не существует!');
        }

        if ($request->isMethod('post')) {

            $token = check($request->input('token'));
            $title = check($request->input('title'));
            $text  = check($request->input('text'));
            $tags  = check($request->input('tags'));

            $validator
                ->equal($token, $_SESSION['token'], trans('validator.token'))
                ->length($title, 5, 50, ['title' => trans('validator.title')])
                ->length($text, 100, setting('maxblogpost'), ['text' => trans('validator.text')])
                ->length($tags, 2, 50, ['tags' => 'Слишком длинные или короткие метки статьи!']);

            if ($validator->isValid()) {

                $blog->update([
                    'title' => $title,
                    'text'  => $text,
                    'tags'  => $tags,
                ]);

                setFlash('success', 'Статья успешно отредактирована!');
                redirect('/articles/'.$blog->id);
            } else {
                setInput($request->all());
                setFlash('danger', $validator->getErrors());
            }
        }

        $categories = Category::query()
            ->where('parent_id', 0)
            ->with('children')
            ->orderBy('sort')
            ->get();

        return view('admin/blogs/edit_blog', compact('blog', 'categories'));
    }

    /**
     * Перенос статьи
     *
     * @param int       $id
     * @param Request   $request
     * @param Validator $validator
     * @return string
     */
    public function moveBlog(int $id, Request $request, Validator $validator): string
    {
        /** @var Blog $blog */
        $blog = Blog::query()->find($id);

        if (! $blog) {
            abort(404, 'Данной статьи не существует!');
        }

        if ($request->isMethod('post')) {

            $token = check($request->input('token'));
            $cid   = int($request->input('cid'));

            /** @var Category $category */
            $category = Category::query()->find($cid);

            $validator
                ->equal($token, $_SESSION['token'], trans('validator.token'))
                ->notEmpty($category, ['cid' => 'Категории для статьи не существует!']);

            if ($category) {
                $validator->empty($category->closed, ['cid' => 'В данном разделе запрещено создавать статьи!']);
                $validator->notEqual($blog->category_id, $category->id, ['cid' => 'Нельзя переносить статью в этот же раздел!']);
            }

            if ($validator->isValid()) {

                // Обновление счетчиков
                $category->increment('count_blogs');
                Category::query()->where('id', $blog->category_id)->decrement('count_blogs');

                $blog->update([
                    'category_id' => $category->id,
                ]);

                setFlash('success', 'Статья успешно перенесена!');
                redirect('/articles/'.$blog->id);
            } else {
                setInput($request->all());
                setFlash('danger', $validator->getErrors());
            }
        }

        $categories = Category::query()
            ->where('parent_id', 0)
            ->with('children')
            ->orderBy('sort')
            ->get();

        return view('admin/blogs/move_blog', compact('blog', 'categories'));
    }

    /**
     * Удаление статьи
     *
     * @param int       $id
     * @param Request   $request
     * @param Validator $validator
     * @return void
     * @throws \Exception
     */
    public function deleteBlog(int $id, Request $request, Validator $validator): void
    {
        $page  = int($request->input('page', 1));
        $token = check($request->input('token'));

        /** @var Blog $blog */
        $blog = Blog::query()->find($id);

        if (! $blog) {
            abort(404, 'Данной статьи не существует!');
        }

        $validator->equal($token, $_SESSION['token'], trans('validator.token'));

        if ($validator->isValid()) {

            $blog->comments()->delete();
            $blog->delete();

            $blog->category->decrement('count_blogs');

            setFlash('success', 'Статья успешно удалена!');
        } else {
            setFlash('danger', $validator->getErrors());
        }

        redirect('/admin/blogs/' . $blog->category_id . '?page=' . $page);
    }
}
