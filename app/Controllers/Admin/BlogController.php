<?php

namespace App\Controllers\Admin;

use App\Classes\Request;
use App\Classes\Validator;
use App\Models\Blog;
use App\Models\Category;
use App\Models\User;

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
     * @return void
     */
    public function create(): void
    {
        if (! isAdmin(User::BOSS)) {
            abort(403, 'Доступ запрещен!');
        }

        $token = check(Request::input('token'));
        $name  = check(Request::input('name'));

        $validator = new Validator();
        $validator->equal($token, $_SESSION['token'], 'Неверный идентификатор сессии, повторите действие!')
            ->length($name, 3, 50, ['name' => 'Слишком длинное или короткое название раздела!']);

        if ($validator->isValid()) {

            $max = Category::query()->max('sort') + 1;

            $category = Category::query()->create([
                'name'  => $name,
                'sort'  => $max,
            ]);

            setFlash('success', 'Новый раздел успешно создан!');
            redirect('/admin/blogs/edit/' . $category->id);
        } else {
            setInput(Request::all());
            setFlash('danger', $validator->getErrors());
        }

        redirect('/admin/blogs');
    }

    /**
     * Редактирование раздела
     *
     * @param int $id
     * @return string
     */
    public function edit($id): string
    {
        if (! isAdmin(User::BOSS)) {
            abort(403, 'Доступ запрещен!');
        }

        $category = Category::query()->with('children')->find($id);

        if (! $category) {
            abort(404, 'Данного раздела не существует!');
        }

        $categories = Category::query()
            ->where('parent_id', 0)
            ->orderBy('sort')
            ->get();

        if (Request::isMethod('post')) {
            $token  = check(Request::input('token'));
            $parent = int(Request::input('parent'));
            $name   = check(Request::input('name'));
            $sort   = check(Request::input('sort'));
            $closed = empty(Request::input('closed')) ? 0 : 1;

            $validator = new Validator();
            $validator->equal($token, $_SESSION['token'], 'Неверный идентификатор сессии, повторите действие!')
                ->length($name, 3, 50, ['title' => 'Слишком длинное или короткое название раздела!'])
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
                setInput(Request::all());
                setFlash('danger', $validator->getErrors());
            }
        }

        return view('admin/blogs/edit', compact('categories', 'category'));
    }

    /**
     * Удаление раздела
     *
     * @param int $id
     * @return void
     * @throws \Exception
     */
    public function delete($id): void
    {
        if (! isAdmin(User::BOSS)) {
            abort(403, 'Доступ запрещен!');
        }

        $category = Category::query()->with('children')->find($id);

        if (! $category) {
            abort(404, 'Данного раздела не существует!');
        }

        $token = check(Request::input('token'));

        $validator = new Validator();
        $validator->equal($token, $_SESSION['token'], 'Неверный идентификатор сессии, повторите действие!')
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
     * @return void
     */
    public function restatement(): void
    {
        if (! isAdmin(User::BOSS)) {
            abort(403, 'Доступ запрещен!');
        }

        $token = check(Request::input('token'));

        if ($token === $_SESSION['token']) {

            restatement('blogs');

            setFlash('success', 'Данные успешно пересчитаны!');
        } else {
            setFlash('danger', 'Ошибка! Неверный идентификатор сессии, повторите действие!');
        }

        redirect('/admin/blogs');
    }

    /**
     * Список блогов
     *
     * @param int $id
     * @return string
     */
    public function blog($id): string
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
     * @param int $id
     * @return string
     */
    public function editBlog($id): string
    {
        $blog = Blog::query()->find($id);

        if (! $blog) {
            abort(404, 'Данной статьи не существует!');
        }

        if (Request::isMethod('post')) {

            $token = check(Request::input('token'));
            $title = check(Request::input('title'));
            $text  = check(Request::input('text'));
            $tags  = check(Request::input('tags'));

            $validator = new Validator();
            $validator
                ->equal($token, $_SESSION['token'], 'Неверный идентификатор сессии, повторите действие!')
                ->length($title, 5, 50, ['title' => 'Слишком длинный или короткий заголовок!'])
                ->length($text, 100, setting('maxblogpost'), ['text' => 'Слишком длинный или короткий текст статьи!'])
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
                setInput(Request::all());
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
     * @param int $id
     * @return string
     */
    public function moveBlog($id): string
    {
        $blog = Blog::query()->find($id);

        if (! $blog) {
            abort(404, 'Данной статьи не существует!');
        }

        if (Request::isMethod('post')) {

            $token = check(Request::input('token'));
            $cid   = int(Request::input('cid'));

            $category = Category::query()->find($cid);

            $validator = new Validator();
            $validator
                ->equal($token, $_SESSION['token'], 'Неверный идентификатор сессии, повторите действие!')
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
                setInput(Request::all());
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
     * @param int $id
     * @return void
     * @throws \Exception
     */
    public function deleteBlog($id): void
    {
        $page  = int(Request::input('page', 1));
        $token = check(Request::input('token'));

        $blog = Blog::query()->find($id);

        if (! $blog) {
            abort(404, 'Данной статьи не существует!');
        }

        $validator = new Validator();
        $validator->equal($token, $_SESSION['token'], 'Неверный идентификатор сессии, повторите действие!');

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
