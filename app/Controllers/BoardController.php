<?php

namespace App\Controllers;

use App\Classes\Request;
use App\Classes\Validator;
use App\Models\Board;
use App\Models\Item;

class BoardController extends BaseController
{
    /**
     * Главная страница
     */
    public function index()
    {
        $total = Item::query()->count();
        $page = paginate(10, $total);

        $items = Item::query()
            ->orderBy('created_at', 'desc')
            ->limit($page->limit)
            ->offset($page->offset)
            ->with('category', 'user')
            ->get();

        return view('boards/index', compact('items', 'page'));
    }
}
