<?php

declare(strict_types=1);

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
            ->orderByDesc($order)
            ->with('category', 'user')
            ->paginate(setting('downlist'))
            ->appends(['sort' => $sort]);

        return view('loads/top', compact('downs', 'order'));
    }
}

