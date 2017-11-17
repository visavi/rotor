<?php

namespace App\Controllers\Load;

use App\Classes\Request;
use App\Controllers\BaseController;
use App\Models\Down;
use App\Models\Load;

class LoadController extends BaseController
{
    /**
     * Главная страница
     */
    public function index()
    {
        $cats = Load::query()
            ->where('parent_id', 0)
            ->with('children', 'new', 'children.new')
            ->orderBy('sort')
            ->get();

        if (empty(count($cats))) {
            abort('default', 'Разделы загрузок еще не созданы!');
        }

        $new = Down::query()
            ->where('active', 1)
            ->where('created_at', '>', SITETIME - 3600 * 120)
            ->count();

        return view('load/index', compact('cats', 'new'));
    }

    /**
     * Список файлов в категории
     */
    public function load($id)
    {
        $category = Load::query()->with('parent')->find($id);

        if (! $category) {
            abort(404, 'Данной категории не существует!');
        }

        $total = Down::query()->where('category_id', $category->id)->where('active', 1)->count();
        $page = paginate(setting('downlist'), $total);

        $sort = check(Request::input('sort'));

        switch ($sort) {
            case 'rated':
                $order = 'rated';
                break;
            case 'comments':
                $order = 'comments';
                break;
            case 'loads':
                $order = 'loads';
                break;
            default:
                $order = 'created_at';
        }

        $downs = Down::query()
            ->where('category_id', $category->id)
            ->where('active', 1)
            ->orderBy($order, 'desc')
            ->offset($page['offset'])
            ->limit($page['limit'])
            ->get();

        return view('load/load', compact('category', 'downs', 'page', 'order'));
    }

    /**
     * RSS всех файлов
     */
    public function rss()
    {
        $downs = Down::query()
            ->orderBy('created_at', 'desc')
            ->limit(15)
            ->get();

        if ($downs->isEmpty()) {
            abort('default', 'Блоги не найдены!');
        }

        return view('load/rss', compact('downs'));
    }
}
