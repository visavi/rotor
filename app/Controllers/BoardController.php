<?php

namespace App\Controllers;

use App\Classes\Request;
use App\Classes\Validator;
use App\Models\Board;


class BoardController extends BaseController
{
    /**
     * Главная страница
     */
    public function index()
    {
        $items = Item::query()->limit(10)->get();


        return view('boards/index', compact('items'));
    }
}
