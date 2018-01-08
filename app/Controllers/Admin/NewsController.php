<?php

namespace App\Controllers\Admin;

use App\Classes\Request;
use App\Classes\Validator;
use App\Models\News;
use App\Models\User;

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
}
