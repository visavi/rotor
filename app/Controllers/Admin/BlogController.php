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
     */
    public function index()
    {
        $categories = Category::query()
            ->where('parent_id', 0)
            ->orderBy('sort')
            ->with('children', 'new', 'children.new')
            ->get();

        if (! $categories) {
            abort('default', 'Разделы блогов еще не созданы!');
        }

        return view('admin/blog/index', compact('categories'));
    }

    /**
     * Создание раздела
     */
    public function create()
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
            redirect('/admin/blog/edit/' . $category->id);
        } else {
            setInput(Request::all());
            setFlash('danger', $validator->getErrors());
        }

        redirect('/admin/blog');
    }

    /**
     * Редактирование раздела
     */
    public function edit($id)
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
                redirect('/admin/blog');
            } else {
                setInput(Request::all());
                setFlash('danger', $validator->getErrors());
            }
        }

        return view('admin/blog/edit', compact('categories', 'category'));
    }

    /**
     * Удаление раздела
     */
    public function delete($id)
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

        redirect('/admin/blog');
    }

    /**
     * Пересчет данных
     */
    public function restatement()
    {
        if (! isAdmin(User::BOSS)) {
            abort(403, 'Доступ запрещен!');
        }

        $token = check(Request::input('token'));

        if ($token == $_SESSION['token']) {

            restatement('blog');

            setFlash('success', 'Данные успешно пересчитаны!');
        } else {
            setFlash('danger', 'Ошибка! Неверный идентификатор сессии, повторите действие!');
        }

        redirect('/admin/blog');
    }

    /**
     * Список блогов
     */
    public function blog($id)
    {
        $category = Category::query()->with('parent')->find($id);

        if (! $category) {
            abort('default', 'Данного раздела не существует!');
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

        return view('admin/blog/blog', compact('blogs', 'category', 'page'));
    }

    /**
     * Редактирование статьи
     */
    public function editBlog($id)
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
                    'title'       => $title,
                    'text'        => $text,
                    'tags'        => $tags,
                ]);

                setFlash('success', 'Статья успешно отредактирована!');
                redirect('/article/'.$blog->id);
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

        return view('admin/blog/edit_blog', compact('blog', 'categories'));
    }

    /**
     * Перенос статьи
     */
    public function moveBlog($id)
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
                redirect('/article/'.$blog->id);
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

        return view('admin/blog/move_blog', compact('blog', 'categories'));
    }

    /**
     * Удаление тем
     */
    public function deleteBlog($id)
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

        redirect('/admin/blog/' . $blog->category_id . '?page=' . $page);
    }
}
