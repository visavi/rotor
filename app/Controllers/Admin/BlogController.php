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
}
