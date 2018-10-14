<?php

namespace App\Controllers\Load;

use App\Controllers\BaseController;
use App\Models\Down;
use Illuminate\Http\Request;

class TopController extends BaseController
{
    /**
     * Топ файлов
     *
     * @param Request $request
     * @return string
     */
    public function index(Request $request): string
    {
        $total = Down::query()->where('active', 1)->count();

        if ($total > 500) {
            $total = 500;
        }

        $page = paginate(setting('downlist'), $total);

        $sort = check($request->input('sort'));

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
            ->limit($page->limit)
            ->offset($page->offset)
            ->with('category', 'user')
            ->get();

        return view('loads/top', compact('downs', 'page', 'order'));
    }
}

