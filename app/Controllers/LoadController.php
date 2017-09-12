<?php

namespace App\Controllers;

use App\Models\Cats;
use App\Models\Down;

class LoadController extends BaseController
{
    /**
     * Главная страница
     */
    public function index()
    {
        $cats = Cats::where('parent_id', 0)
            ->with('children', 'new', 'children.new')
            ->orderBy('sort')
            ->get();

        if (empty(count($cats))) {
            abort('default', 'Разделы загрузок еще не созданы!');
        }

        $new = Down::where('active', 1)
            ->where('created_at', '>', SITETIME-3600 * 120)
            ->count();

        return view('load/index', compact('cats', 'new'));
    }
}
