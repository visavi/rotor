<?php

namespace App\Controllers\Load;

use App\Classes\Request;
use App\Controllers\BaseController;
use App\Models\Comment;
use App\Models\Down;
use App\Models\User;

class TopController extends BaseController
{
    /**
     * Топ файлов
     */
    public function index()
    {
        $total = Down::query()->where('active', 1)->count();

        if ($total > 500) {
            $total = 500;
        }

        $page = paginate(setting('downlist'), $total);

        $sort = check(Request::input('sort'));

        switch ($sort) {
            case 'rated':
                $order = 'rated';
                break;
            case 'comments':
                $order = 'count_comments';
                break;
            default:
                $order = 'loads';
        }

        $downs = Down::query()
            ->where('active', 1)
            ->orderBy($order, 'desc')
            ->limit($page['limit'])
            ->offset($page['offset'])
            ->with('category', 'user')
            ->get();

        return view('load/top', compact('downs', 'page', 'order'));
    }
}

