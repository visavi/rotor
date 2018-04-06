<?php

namespace App\Controllers\User;

use App\Classes\Request;
use App\Controllers\BaseController;
use App\Models\User;

class SearchController extends BaseController
{
    /**
     * Главная страница
     */
    public function index()
    {
        return view('user/search');
    }

    /**
     * Поиск пользователя
     */
    public function search()
    {
        $find = check(Request::input('find'));

        if (utfStrlen($find) < 3 || utfStrlen($find) > 20) {
            setInput(Request::all());
            setFlash('danger', ['find' => 'Слишком короткий или длинный запрос, от 3 до 20 символов!']);
            redirect('/searchuser');
        }

        $users = User::query()
            ->where('login LIKE "%' . $find . '%"')
            ->orWhere('name LIKE "%' . $find . '%"')
            ->orderBy('point', 'desc')
            ->limit(setting('usersearch'))
            ->get();

        return view('user/search_result', compact('users'));
    }

    /**
     * Поиск по первой букве
     */
    public function sort($letter)
    {
        $search = is_numeric($letter) ? "RLIKE '^[-0-9]'" : "LIKE '$letter%'";

        $total = User::query()
            ->whereRaw('login ' . $search)
            ->count();

        $page = paginate(setting('usersearch'), $total);

        $users = User::query()
            ->whereRaw('login ' . $search)
            ->orderBy('point', 'desc')
            ->offset($page->offset)
            ->limit($page->limit)
            ->get();

        return view('user/search_sort', compact('users', 'page'));
    }
}
